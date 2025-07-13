<?php
// 맨 처음에 DB 설정 파일을 포함시킵니다.
// require_once 'DB.php'; // 실제 DB 클래스 파일 경로에 맞게 수정하세요.
require_once "../db.php";

try {
    // ◀◀ [수정 1] 스크립트 최상단에서 DB 연결을 먼저 시도합니다.
    $pdo = DB::getDB();

    // DB::getDB()가 실패하여 null을 반환하는 경우를 대비한 방어 코드
    if ($pdo === null) {
        throw new Exception("데이터베이스 연결 객체를 받아오지 못했습니다. DB 설정 파일을 확인하세요.");
    }

} catch (PDOException $e) {
    // DB 연결 정보가 잘못되었을 때 발생하는 오류
    die("데이터베이스 연결 오류: " . $e->getMessage());
} catch (Exception $e) {
    // 그 외의 오류
    die("오류: " . $e->getMessage());
}


// 이제 $pdo 변수는 유효한 연결 객체를 담고 있습니다.
// 파라미터 유무에 따라 로직을 분기합니다.

if (isset($_GET['category']) && !empty($_GET['category']) && isset($_GET['budget']) && !empty($_GET['budget'])) {

    // [파라미터가 있는 경우]
    $cate = $_GET['category'];
    $budget = $_GET['budget'];

    // [수정 2] 이미 연결된 $pdo 객체를 바로 사용하므로 try-catch가 여기서 필요 없습니다.
    $sql_restaurants = "
        SELECT DISTINCT r.* FROM restaurants r
        JOIN menu_items m ON r.place_id = m.place_id
        WHERE r.category = :category
          AND CAST(REPLACE(REPLACE(m.price, ',', ''), '원', '') AS UNSIGNED) <= :budget
        ORDER BY r.overall_rating DESC
    ";

    $stmt_restaurants = $pdo->prepare($sql_restaurants); // 오류가 발생했던 라인
    $stmt_restaurants->execute([
        ':category' => $cate,
        ':budget' => $budget
    ]);
    $restaurants = $stmt_restaurants->fetchAll();

} else {

    // [파라미터가 없는 경우]
    // [수정 3] 여기서도 동일한 $pdo 객체를 사용하여 일관성을 유지합니다.
    $stmt = $pdo->query("SELECT * FROM restaurants ORDER BY RAND() LIMIT 10");
    $restaurants = $stmt->fetchAll();
}

// 이 아래에서 $restaurants 변수를 사용하여 화면에 결과를 출력합니다.
// ...

?>

<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>오밥추 - 추천 결과</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&family=Noto+Sans+KR:wght@400;500;700&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="../style/stroe.css">
</head>

<body>

    <div class="main-container">

        <div class="left-panel"></div>

        <div class="right-panel">

            <div>
                <header class="content-header">
                    <h1>Ohbabchu</h1>
                    <h2>restaurants</h2>
                </header>

                <div class="restaurants-list">

                    <?php if (empty($restaurants)): ?>
                        <p class="no-results">선택하신 조건에 맞는 식당이 없습니다.</p>
                    <?php else: ?>
                        <?php foreach ($restaurants as $restaurant): ?>
                            <?php
                            // 2. 각 식당의 메뉴 4개를 가져옵니다.
                            $sql_menus = "SELECT name, price FROM menu_items WHERE place_id = :place_id LIMIT 4";
                            $stmt_menus = $pdo->prepare($sql_menus);
                            $stmt_menus->execute([':place_id' => $restaurant->place_id]);
                            $menus = $stmt_menus->fetchAll();
                            ?>

                            <div class="restaurant-card">
                                <div class="card-header">
                                    <h3><a
                                            href="../map/mapShow.php?id=<?= htmlspecialchars($restaurant->place_id, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($restaurant->restaurant_name, ENT_QUOTES, 'UTF-8') ?></a>
                                    </h3>
                                    <div class="rating">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path
                                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                        <span><?= htmlspecialchars($restaurant->overall_rating ?? '-', ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                </div>
                                <div class="menu-items">
                                    <?php if (empty($menus)): ?>
                                        <p class="no-menu">메뉴 정보가 없습니다.</p>
                                    <?php else: ?>
                                        <?php foreach ($menus as $menu): ?>
                                            <div class="menu-item">
                                                <p class="menu-title"><?= htmlspecialchars($menu->name, ENT_QUOTES, 'UTF-8') ?></p>
                                                <p class="menu-desc"><?= htmlspecialchars($menu->price, ENT_QUOTES, 'UTF-8') ?></p>
                                            </div>
                                        <?php endforeach; ?>

                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                </div>
            </div>


        </div>
    </div>
</body>

</html>