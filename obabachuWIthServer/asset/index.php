<?php
session_start();
header("Content-Type: text/html; charset=utf-8");

/**
 * 사용자 제공 DB 클래스
 */
class DB
{
    static $db = null;
    static function getDB()
    {
        if (!self::$db) {
            // 중요: 'obabachu'는 데이터베이스 이름입니다. 자신의 환경에 맞게 수정해주세요.
            // 중요: 'root', ''는 DB 사용자 이름과 비밀번호입니다. 자신의 환경에 맞게 수정해주세요.
            $connection = new PDO("mysql:host=localhost;dbname=obabachu;charset=utf8mb4", "root", "");
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            self::$db = $connection;
        }
        return self::$db;
    }

    // exec와 fetch, fetchAll은 이 스크립트에서 직접 사용하지 않으므로 그대로 둡니다.
    static function exec($query)
    {
        try {
            self::getDB()->exec($query);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    static function fetch($query)
    {
        $stmt = self::getDB()->query($query);
        return $stmt->fetch();
    }
    static function fetchAll($query)
    {
        $stmt = self::getDB()->query($query);
        return $stmt->fetchAll();
    }
}


// --- 데이터 삽입 로직 시작 ---

// 1. JSON 파일 읽기
$json_file = 'data.json';
if (!file_exists($json_file)) {
    die("오류: data.json 파일을 찾을 수 없습니다.");
}
$json_data = file_get_contents($json_file);
$restaurants = json_decode($json_data);

if ($restaurants === null) {
    die("오류: JSON 데이터를 디코딩할 수 없습니다. 파일 형식을 확인해주세요.");
}

// 2. 데이터베이스 연결
$pdo = DB::getDB();

// 3. 각 식당 정보를 순회하며 데이터베이스에 삽입
foreach ($restaurants as $restaurant) {
    // 데이터 무결성을 위해 트랜잭션 시작
    $pdo->beginTransaction();

    try {
        // 3-1. 'restaurants' 테이블에 데이터 삽입
        $sql_restaurant = "INSERT INTO restaurants (place_id, restaurant_name, category, address, phone_number, overall_rating, latitude, longitude, kakao_map_url, crawled_at, data_version) 
                           VALUES (:place_id, :restaurant_name, :category, :address, :phone_number, :overall_rating, :latitude, :longitude, :kakao_map_url, :crawled_at, :data_version)";

        $stmt_restaurant = $pdo->prepare($sql_restaurant);
        $stmt_restaurant->execute([
            ':place_id' => $restaurant->place_id,
            ':restaurant_name' => $restaurant->restaurant_name,
            ':category' => $restaurant->category,
            ':address' => $restaurant->address,
            ':phone_number' => $restaurant->phone_number ?? null,
            ':overall_rating' => !empty($restaurant->overall_rating) ? $restaurant->overall_rating : null,
            ':latitude' => $restaurant->location->latitude ?? null,
            ':longitude' => $restaurant->location->longitude ?? null,
            ':kakao_map_url' => $restaurant->kakao_map_url,
            ':crawled_at' => $restaurant->crawled_at,
            ':data_version' => $restaurant->data_version
        ]);

        // 3-2. 'menu_items' 테이블에 데이터 삽입
        if (!empty($restaurant->menu_items)) {
            $sql_menu = "INSERT INTO menu_items (place_id, name, price) VALUES (:place_id, :name, :price)";
            $stmt_menu = $pdo->prepare($sql_menu);

            foreach ($restaurant->menu_items as $item) {
                $stmt_menu->execute([
                    ':place_id' => $restaurant->place_id,
                    ':name' => $item->name,
                    ':price' => $item->price
                ]);
            }
        }

        // 3-3. 'reviews' 테이블에 데이터 삽입
        if (!empty($restaurant->reviews_by_star)) {
            $sql_review = "INSERT INTO reviews (place_id, star_rating, review_text) VALUES (:place_id, :star_rating, :review_text)";
            $stmt_review = $pdo->prepare($sql_review);

            foreach ($restaurant->reviews_by_star as $star => $review_array) {
                foreach ($review_array as $review_text) {
                    $stmt_review->execute([
                        ':place_id' => $restaurant->place_id,
                        ':star_rating' => (int) $star,
                        ':review_text' => $review_text
                    ]);
                }
            }
        }

        // 모든 쿼리가 성공적으로 실행되면 커밋
        $pdo->commit();
        echo "{$restaurant->restaurant_name} (ID: {$restaurant->place_id}) 정보가 성공적으로 삽입되었습니다.<br>";

    } catch (Exception $e) {
        // 오류 발생 시 롤백
        $pdo->rollBack();
        echo "오류 발생: {$restaurant->restaurant_name} (ID: {$restaurant->place_id}) 삽입에 실패했습니다. Error: " . $e->getMessage() . "<br>";
    }
}

echo "<h3>모든 데이터 처리가 완료되었습니다.</h3>";

?>