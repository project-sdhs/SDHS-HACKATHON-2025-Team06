import requests
import json
import sys
import os
import time
import logging
import math
import argparse
import csv
import copy
from queue import Queue, Empty
from datetime import datetime
from dataclasses import dataclass
from typing import Dict, List, Any, Optional
from concurrent.futures import ThreadPoolExecutor, as_completed
from functools import wraps

from bs4 import BeautifulSoup
from tqdm import tqdm
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.remote.webdriver import WebDriver
from selenium.webdriver.remote.webelement import WebElement
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import TimeoutException, WebDriverException

# --- 0. 설정 및 유틸리티 ---

@dataclass(frozen=True)
class Config:
    """스크립트 설정 값을 관리"""
    kakao_api_key: str
    chromedriver_path: str  # chromedriver 경로를 직접 받도록 설정
    max_workers: int
    category_map: Dict[str, List[str]]
    max_crawl_count: int = 10
    scroll_attempts: int = 10
    max_reviews_per_star: int = 3

def setup_logging():
    """스크립트 전역 로깅 시스템 설정"""
    logging.basicConfig(level=logging.INFO, format='%(asctime)s [%(levelname)s] %(message)s', datefmt='%Y-%m-%d %H:%M:%S')

def load_config(path: str = "config.json") -> Config:
    """설정 파일을 로드"""
    if not os.path.exists(path):
        logging.critical(f"'{path}' 설정 파일이 없습니다. 예시 파일을 참고하여 생성해주세요.")
        sys.exit(1)
    with open(path, 'r', encoding='utf-8') as f:
        config_data = json.load(f)
    return Config(**config_data)

def retry(attempts: int = 3, delay: float = 1.0):
    """예외 발생 시 작업을 재시도하는 데코레이터"""
    def decorator(func):
        @wraps(func)
        def wrapper(*args, **kwargs):
            last_exception = None
            for attempt in range(attempts):
                try:
                    return func(*args, **kwargs)
                except Exception as e:
                    last_exception = e
                    logging.warning(f"'{func.__name__}' 작업 실패 (시도 {attempt + 1}/{attempts}). {delay}초 후 재시도. 오류: {e}")
                    time.sleep(delay)
            raise last_exception
        return wrapper
    return decorator

# --- 1. 크롤러 클래스 정의 ---

class KakaoMapCrawler:
    """카카오맵 크롤러 (안정화된 드라이버 풀 및 효율적인 스크롤)"""

    DISCOVERY_API_URL = "https://dapi.kakao.com/v2/local/search/keyword.json"

    SELECTORS = {
        "rating": "span.num_star",
        "menu_tab": 'a.link_tab[href="#menuInfo"]',
        "menu_wrap": "div.wrap_goods",
        "menu_list": 'ul.list_goods > li',
        "menu_name": 'strong.tit_item',
        "menu_price": 'p.desc_item',
        "review_tab": 'a.link_tab[href="#comment"]',
        "review_list_wrap": "ul.list_review",
        "review_item": "ul.list_review > li",
        "review_rating": "span.figure_star.on",
        "review_content": "p.desc_review",
    }

    def __init__(self, config: Config):
        self.config = config
        self.session = requests.Session()
        self.session.headers.update({"Authorization": f"KakaoAK {self.config.kakao_api_key}"})
        self.driver_pool = Queue(maxsize=self.config.max_workers)
        self._initialize_driver_pool()

    def __enter__(self):
        return self

    def __exit__(self, exc_type, exc_val, exc_tb):
        self.close()

    def _create_driver(self) -> WebDriver:
        """새로운 Selenium WebDriver 인스턴스를 생성"""
        try:
            options = Options()
            options.add_experimental_option("prefs", {"profile.managed_default_content_settings.images": 2})
            options.add_argument("--headless"); options.add_argument("--no-sandbox")
            options.add_argument("--disable-dev-shm-usage"); options.add_argument("--log-level=3")
            # 설정 파일의 경로를 사용하여 드라이버 서비스 설정
            service = Service(executable_path=self.config.chromedriver_path)
            driver = webdriver.Chrome(service=service, options=options)
            return driver
        except Exception as e:
            logging.error(f"드라이버 생성 실패: {e}")
            raise

    def _initialize_driver_pool(self):
        """드라이버 풀을 초기화"""
        logging.info(f"Selenium 드라이버 풀 초기화 중... (워커 수: {self.config.max_workers})")
        for _ in range(self.config.max_workers):
            try:
                driver = self._create_driver()
                self.driver_pool.put(driver)
            except Exception:
                continue
        if self.driver_pool.empty():
            logging.critical("사용 가능한 드라이버가 없습니다. Chromedriver 경로를 확인하세요. 스크립트를 종료합니다.")
            sys.exit(1)
        logging.info(f"총 {self.driver_pool.qsize()}개의 드라이버가 성공적으로 생성되었습니다.")

    def discover_restaurants(self, lon: str, lat: str, radius: int, target_count: int) -> List[Dict[str, Any]]:
        """위치 기반 맛집 탐색"""
        center_lon, center_lat = float(lon), float(lat)
        logging.info(f"🗺️  좌표 (경도:{lon}, 위도:{lat}) 반경 {radius}m 내에서 최대 {target_count}개 음식점 검색 시작...")

        unique_restaurants = {}
        lat_offset = (radius / 2) / 111000
        lon_offset = (radius / 2) / (111000 * math.cos(math.radians(center_lat)))
        search_points = [(str(center_lon + j * lon_offset), str(center_lat + i * lat_offset)) for i in range(-1, 2) for j in range(-1, 2)]

        for p_lon, p_lat in search_points:
            if len(unique_restaurants) >= target_count:
                logging.info(f"목표 개수 {target_count}개를 초과하여 탐색을 중단합니다.")
                break
            for page in range(1, 4):
                params = {"query": "맛집", "page": page, "size": 15, "category_group_code": "FD6",
                          'x': p_lon, 'y': p_lat, 'radius': radius, 'sort': 'distance'}
                try:
                    response = self.session.get(self.DISCOVERY_API_URL, params=params, timeout=5)
                    response.raise_for_status()
                    data = response.json()
                    if not data.get('documents'): break
                    for doc in data['documents']:
                        unique_restaurants[doc['id']] = doc
                    if len(unique_restaurants) >= target_count or data['meta']['is_end']: break
                except requests.RequestException as e:
                    logging.error(f"API 요청 오류 (Lon: {p_lon}, Lat: {p_lat}, Page: {page}): {e}")
                    break

        restaurant_list = list(unique_restaurants.values())
        logging.info(f"✅ 총 {len(search_points)}개 지점 탐색 완료. {len(restaurant_list)}개의 고유 크롤링 대상 확정.")
        return restaurant_list

    def _scrape_rating(self, wait: WebDriverWait) -> Optional[str]:
        try:
            rating_tag = wait.until(EC.presence_of_element_located((By.CSS_SELECTOR, self.SELECTORS["rating"])))
            return rating_tag.text
        except TimeoutException:
            return None

    def _scrape_menus(self, driver: WebDriver, wait: WebDriverWait) -> List[Dict[str, str]]:
        menus = []
        try:
            menu_tab = wait.until(EC.element_to_be_clickable((By.CSS_SELECTOR, self.SELECTORS["menu_tab"])))
            menu_tab.click()
            wait.until(EC.presence_of_element_located((By.CSS_SELECTOR, self.SELECTORS["menu_wrap"])))

            soup = BeautifulSoup(driver.page_source, 'html.parser')
            for item in soup.select(self.SELECTORS["menu_list"]):
                name_tag = item.select_one(self.SELECTORS["menu_name"])
                if not name_tag or "더보기" in name_tag.get_text(): continue

                price_tag = item.select_one(self.SELECTORS["menu_price"])
                menus.append({
                    "name": name_tag.get_text(strip=True),
                    "price": price_tag.get_text(strip=True) if price_tag else "가격 정보 없음"
                })
        except TimeoutException:
            pass
        return menus

    def _scrape_reviews(self, driver: WebDriver, wait: WebDriverWait) -> Dict[int, List[str]]:
        """리뷰 스크롤 및 파싱 효율 개선"""
        reviews = {star: [] for star in range(1, 6)}
        try:
            review_tab = wait.until(EC.element_to_be_clickable((By.CSS_SELECTOR, self.SELECTORS["review_tab"])))
            review_tab.click()
            wait.until(EC.presence_of_element_located((By.CSS_SELECTOR, self.SELECTORS["review_list_wrap"])))

            last_review_count = 0
            for _ in range(self.config.scroll_attempts):
                driver.execute_script("window.scrollTo(0, document.body.scrollHeight);")
                time.sleep(0.8)

                current_reviews = driver.find_elements(By.CSS_SELECTOR, self.SELECTORS["review_item"])
                if len(current_reviews) == last_review_count:
                    break
                last_review_count = len(current_reviews)

            all_review_elements = driver.find_elements(By.CSS_SELECTOR, self.SELECTORS["review_item"])
            for review_item_el in all_review_elements:
                soup = BeautifulSoup(review_item_el.get_attribute('outerHTML'), 'html.parser')
                rating = len(soup.select(self.SELECTORS["review_rating"]))
                content_tag = soup.select_one(self.SELECTORS["review_content"])
                content = content_tag.get_text(strip=True) if content_tag else ""

                if rating in reviews and len(reviews[rating]) < self.config.max_reviews_per_star:
                    reviews[rating].append(content)
        except TimeoutException:
            pass
        return reviews

    def _scrape_page_data(self, driver: WebDriver, place_id: str) -> Dict[str, Any]:
        base_url = f"https://place.map.kakao.com/{place_id}"
        data = {"overall_rating": None, "menus": [], "reviews": {}}

        driver.get(base_url)
        wait = WebDriverWait(driver, 10)

        data["overall_rating"] = self._scrape_rating(wait)
        data["menus"] = self._scrape_menus(driver, wait)
        data["reviews"] = self._scrape_reviews(driver, wait)

        return data

    def _simplify_category(self, category_name: Optional[str]) -> str:
        """설정 파일의 카테고리 맵을 사용하여 분류"""
        if not category_name:
            return "기타"
        for simple_cat, keywords in self.config.category_map.items():
            if any(keyword in category_name for keyword in keywords):
                return simple_cat
        return "기타"

    @retry(attempts=2, delay=2)
    def _process_restaurant(self, restaurant_info: Dict[str, Any]) -> Optional[Dict[str, Any]]:
        """개별 레스토랑 처리 (안정적인 드라이버 교체 로직 포함)"""
        place_name, place_id = restaurant_info.get('place_name'), restaurant_info.get('id')
        if not all([place_name, place_id]): return None

        driver = None
        driver_is_faulty = False
        try:
            driver = self.driver_pool.get(timeout=20)
            scraped_data = self._scrape_page_data(driver, place_id)

            detailed_category = restaurant_info.get('category_name')
            simple_category = self._simplify_category(detailed_category)

            return {
                "restaurant_name": place_name, "category": simple_category,
                "overall_rating": scraped_data["overall_rating"],
                "phone_number": restaurant_info.get('phone'),
                "address": restaurant_info.get('road_address_name'),
                "latitude": restaurant_info.get('y'), "longitude": restaurant_info.get('x'),
                "kakao_map_url": restaurant_info.get('place_url'),
                "menu_items": scraped_data["menus"], "reviews_by_star": scraped_data["reviews"],
                "crawled_at": datetime.now().isoformat()
            }
        except WebDriverException as e:
            driver_is_faulty = True
            logging.error(f"'{place_name}' 처리 중 드라이버 오류 발생: {e.msg[:100]}")
            raise e
        except Empty:
            logging.error("드라이버 풀이 비어있어 작업을 처리할 수 없습니다.")
            return None
        except Exception as e:
            logging.error(f"'{place_name}'({place_id}) 처리 중 예외 발생: {e}", exc_info=False)
            raise e
        finally:
            if driver:
                if driver_is_faulty:
                    logging.warning(f"결함이 발생한 드라이버를 종료하고 새 드라이버로 교체합니다.")
                    try:
                        driver.quit()
                        new_driver = self._create_driver()
                        self.driver_pool.put(new_driver)
                    except Exception as create_err:
                        logging.error(f"새 드라이버 생성/추가 실패: {create_err}")
                else:
                    self.driver_pool.put(driver)

    def run(self, lon: str, lat: str, radius: int, max_count: int):
        restaurant_list = self.discover_restaurants(lon, lat, radius, target_count=max_count)
        if not restaurant_list: return

        restaurant_list_to_crawl = restaurant_list[:max_count]
        logging.info(f"탐색된 {len(restaurant_list)}개 중, {len(restaurant_list_to_crawl)}개에 대한 크롤링을 시작합니다.")

        all_results = []
        with ThreadPoolExecutor(max_workers=self.config.max_workers) as executor:
            future_to_info = {executor.submit(self._process_restaurant, info): info for info in restaurant_list_to_crawl}
            for future in tqdm(as_completed(future_to_info), total=len(restaurant_list_to_crawl), desc="크롤링 진행률"):
                try:
                    result = future.result()
                    if result: all_results.append(result)
                except Exception as e:
                    info = future_to_info[future]
                    logging.error(f"'{info.get('place_name')}' 최종 처리 실패: {e.__class__.__name__}")

        if all_results:
            self.save_results(all_results)
        else:
            logging.warning("유효한 크롤링 결과가 없어 파일을 저장하지 않았습니다.")

        logging.info("🎉 모든 작업이 완료되었습니다.")

    def save_results(self, results: List[Dict[str, Any]]):
        output_dir = "json_output"
        os.makedirs(output_dir, exist_ok=True)
        timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
        base_filename = os.path.join(output_dir, f"search_results_{timestamp}")

        # JSON 저장
        json_filename = f"{base_filename}.json"
        with open(json_filename, "w", encoding="utf-8") as f:
            json.dump(results, f, ensure_ascii=False, indent=4)
        logging.info(f"✅ 성공! {len(results)}개 음식점 정보를 '{json_filename}'에 저장했습니다.")

        # CSV 저장
        if not results: return
        csv_filename = f"{base_filename}.csv"
        results_for_csv = copy.deepcopy(results)
        for item in results_for_csv:
            item['menu_items'] = json.dumps(item['menu_items'], ensure_ascii=False)
            item['reviews_by_star'] = json.dumps(item['reviews_by_star'], ensure_ascii=False)

        fieldnames = results_for_csv[0].keys()
        with open(csv_filename, "w", encoding="utf-8-sig", newline="") as f:
            writer = csv.DictWriter(f, fieldnames=fieldnames)
            writer.writeheader()
            writer.writerows(results_for_csv)
        logging.info(f"✅ 성공! 동일한 정보를 '{csv_filename}'에도 저장했습니다.")

    def close(self):
        logging.info("모든 Selenium 드라이버를 종료합니다...")
        while not self.driver_pool.empty():
            try:
                driver = self.driver_pool.get_nowait()
                driver.quit()
            except Empty: break
        self.session.close()
        logging.info("정리 완료.")

# --- 2. 메인 실행 로직 ---
def main():
    setup_logging()

    parser = argparse.ArgumentParser(description="카카오맵 맛집 정보 크롤러 (수동 경로 설정)")
    parser.add_argument("--lon", type=str, default="126.9648", help="중심 경도 (예: 126.9648 for 용산역)")
    parser.add_argument("--lat", type=str, default="37.5296", help="중심 위도 (예: 37.5296 for 용산역)")
    parser.add_argument("--radius", type=int, default=2000, help="검색 반경(미터). 기본값 2000 (2km)")
    parser.add_argument("--count", type=int, help="최대 크롤링할 식당 수 (설정 파일의 값을 덮어씀)")
    args = parser.parse_args()

    config = load_config()

    max_crawl_count = args.count if args.count is not None else config.max_crawl_count

    with KakaoMapCrawler(config) as crawler:
        crawler.run(lon=args.lon, lat=args.lat, radius=args.radius, max_count=max_crawl_count)

if __name__ == "__main__":
    main()
