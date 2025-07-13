<?php
require_once "../db.php";

$history = DB::fetchAll("SELECT * FROM history WHERE user = '{$_SESSION['ss']->name}' ORDER BY date DESC");




?>

<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>오밥추 - 추천 결과</title>

    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&family=Noto+Sans+KR:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../style/stroe.css">

</head>

<body>

    <div class="main-container">

        <!-- 왼쪽 이미지 패널 -->
        <div class="left-panel"></div>

        <!-- 오른쪽 콘텐츠 패널 -->
        <div class="right-panel">

            <div>
                <header class="content-header">
                    <h1>Ohbabchu</h1>
                    <h2>history</h2>
                </header>

                <!-- 식당 목록 -->
                <div class="restaurants-list">


                    <!-- 첫 번째 식당 카드 (예시) -->

                    <?php foreach ($history as $key) {
                        $name = DB::fetch("SELECT restaurant_name FROM restaurants WHERE place_id = '{$key->storeId}'");
                        ?>

                        <div class="restaurant-card">
                            <div class="card-header">
                                <h3><a
                                        href="../map/mapShow.php?id=<?php echo $key->storeId; ?>"><?php echo $name->restaurant_name; ?></a>
                                </h3>
                                <div class="rating">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                    <span>4.8</span>
                                </div>
                            </div>
                            <div class="menu-items">
                                <div class="menu-item">
                                    <p class="menu-title">전주비빔밥</p>
                                    <p class="menu-desc">12,000원</p>
                                </div>
                                <div class="menu-item">
                                    <p class="menu-title">해물파전</p>
                                    <p class="menu-desc">18,000원</p>
                                </div>
                                <div class="menu-item">
                                    <p class="menu-title">갈비찜</p>
                                    <p class="menu-desc">35,000원</p>
                                </div>
                            </div>
                        </div>

                    <?php } ?>


                    <!-- 두 번째 식당 카드 (예시) -->
                    <div class="restaurant-card">
                        <div class="card-header">
                            <h3><a href="./store.html">소담 (Sodam)</a></h3>
                            <div class="rating">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <span>4.5</span>
                            </div>
                        </div>
                        <div class="menu-items">
                            <div class="menu-item">
                                <p class="menu-title">차돌된장찌개</p>
                                <p class="menu-desc">9,000원</p>
                            </div>
                            <div class="menu-item">
                                <p class="menu-title">고추장삼겹살</p>
                                <p class="menu-desc">15,000원</p>
                            </div>
                            <div class="menu-item">
                                <p class="menu-title">김치전</p>
                                <p class="menu-desc">13,000원</p>
                            </div>
                            <div class="menu-item">
                                <p class="menu-title">계란찜</p>
                                <p class="menu-desc">5,000원</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 푸터 영역 -->
            <footer class="content-footer">
                <a href="../recommend/choose.html" class="history-link">recommend</a>

            </footer>

        </div>
    </div>
</body>

</html>