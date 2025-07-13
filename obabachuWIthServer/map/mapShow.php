<?php
require_once "../db.php";

$_SESSION['ss']->name;
$place_id = $_GET['id'] ?? 1;
$storeInfos = DB::fetch("SELECT * FROM restaurants where place_id = '$place_id'");
$foods = DB::fetchAll("SELECT * FROM menu_items where place_id = '$place_id'");
DB::exec("INSERT INTO history (user, storeId) VALUES ('{$_SESSION['ss']->name}', '$place_id')");
$name = $storeInfos->restaurant_name;
$up = $storeInfos->latitude;
$side = $storeInfos->longitude;






?>



<!DOCTYPE html>
<html lang="ko">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>오늘 뭐 먹지? - 랜덤 음식 추천</title>
  <link rel="stylesheet" href="../style/map.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <script type="text/javascript" src="//dapi.kakao.com/v2/maps/sdk.js?appkey=d5d7efa1ad41628743fc4719d62b9b2b"></script>
</head>

<body>
  <div class="wechi">
    <div class="wechi-content">
      <h1>위치를 서울디지텍을 하실건가요 아니면 자동으로 잡히게 할건가요?</h1>
      <div class="button-group">
        <button class="yes-button" onclick="select('서울디지텍')">서울디지텍으로 잡기</button>
        <button class="no-button" onclick="select('자동')">자동으로 잡기</button>
      </div>
    </div>
  </div>
  <iframe src="https://map.kakao.com/link/from/서울디지텍,37.539066,126.990455/to/<?= $name ?>,<?= $up ?>,<?= $side ?>"
    frameborder="0" width="100%" height="100vh" class="map"></iframe>

  <div class="container">



    <section class="menu-info">
      <h2><i class="fa-solid fa-book-open"></i> 메뉴 정보</h2>
      <div class="menu-list">

        <?php foreach ($foods as $food): ?>
          <div class="menu-item">
            <h3><?= htmlspecialchars($food->name) ?></h3>

            <?php
            $numeric_price = (float) str_replace([',', '원'], '', $food->price);
            ?>
            <p class="price"><?= number_format($numeric_price) ?>원</p>

          </div>
        <?php endforeach; ?>
      </div>
    </section>

  </div>
  </section>
  </div>


</body>

</html>