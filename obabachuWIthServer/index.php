<?php
require_once "db.php";

$user = $_SESSION["ss"] ?? null;

print_r($user);

?>

<!DOCTYPE html>
<html lang="ko">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ohbabchu</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Poppins:wght@400;700&display=swap"
    rel="stylesheet">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

  <link rel="stylesheet" href="./style/style.css">
</head>

<body>

  <div class="dots-wrapper">
    <div class="dot op1"></div>
    <div class="dot op05"></div>
    <div class="dot op05"></div>
    <div class="dot op05"></div>
  </div>

  <div class="background-overlay"></div>
  <div class="container ohbabchu-section page">
    <header class="header">
      <div class="logo">Ohbabchu </div>
      <nav class="auth-nav">
        <?php if ($user == null): ?>
          <button class="sign-in" id="sign-in" onclick="location.href='./member/login.php'">sign in</button>
          <button class="sign-up" onclick="location.href='./member/register.php'">sign up</button>
        <?php else: ?>
          <button class="welcome-message"> <?php echo htmlspecialchars($user->name); ?></button>
          <button class="logout" onclick="location.href='./logout.php'">Logout</button>
        <?php endif; ?>
      </nav>
    </header>

    <main class="hero-section">
      <div class="content">

        <div class="content-wrapper">

          <div class="text-content">
            <p class="recommendation-text">Today's meal recommendation</p>
            <h1 class="main-title">Ohbabchu  </h1>
            <p class="slogan">Choose what to eat!</p>
          </div>
        </div>
      </div>
    </main>
  </div>

  <section class="features-main-content page">



    <div class="features-display-area ">
      <div class="content">
        <div class="round-bg"></div>

        <div class="features-header">
          <h2 class="features-title">Ohbabchu has these features!</h2>
          <p class="features-subtitle">Take advantage of three features!</p>
        </div>

        <div class="features-cards-container">

          <div class="feature-card">
            <div class="card-content">
              <h3 class="card-title">RANDOM</h3>
              <p class="card-description">recommend today's menu at random.</p>
              <?php if ($user == null): ?>
                <a href="#" onclick="alert('로그인을 해주세요');" class="card-link"><i class="fas fa-arrow-right"></i></a>
              <?php else: ?>
                <a href="./showStore/store.php" class="card-link"><i class="fas fa-arrow-right"></i></a>
              <?php endif; ?>

            </div>
          </div>

          <div class="feature-card">
            <div class="card-content">
              <h3 class="card-title">MENU</h3>
              <p class="card-description">recommend today's menu in order of preference.</p>
              <?php if ($user == null): ?>
                <a href="#" onclick="alert('로그인을 해주세요');" class="card-link"><i class="fas fa-arrow-right"></i></a>
              <?php else: ?>
                <a href="./recommend/choose.html" class="card-link"><i class="fas fa-arrow-right"></i></a>
              <?php endif; ?>
            </div>
          </div>

          <div class="feature-card">
            <div class="card-content">
              <h3 class="card-title">HISTORY</h3>
              <p class="card-description">Gets the history of the recommended menu.</p>
              <?php if ($user == null): ?>
                <a href="#" onclick="alert('로그인을 해주세요');" class="card-link"><i class="fas fa-arrow-right"></i></a>
              <?php else: ?>
                <a href="./showStore/history.php" class="card-link"><i class="fas fa-arrow-right"></i></a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

    </div>

  </section>

  <div class="card page">
    <div class="content">
      <div class="card-left">
        <h2 class="headline-secondary">
          When you don't know what to eat?
        </h2>
        <h1 class="headline-primary">
          Try Ohbabchu!
        </h1>
        <a href="./recommend/choose.html" class="recommendation-button">
          Recommendation
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
          </svg>
        </a>
      </div>

      <div class="card-right">
        <img src="./map/img/분식.webp" class="food-image">
      </div>

    </div>

  </div>


  <footer class="footer-container page">

    <div class="food-image-wrapper">
      <img src="./map/img/budae.png" alt="">
    </div>

    <div class="content-wrapper">
      <div class="header-section">
        <div class="logo-section">
          <p class="slogan">I hope you had a good meal today</p>
          <h1 class="logo-text">Ohbabchu</h1>
        </div>

        <div class="member-section">
          <h3 class="member-title">MEMBER</h3>
          <ul class="member-list">
            <li>leader | 정태민</li>
            <li>member1 | 정동일</li>
            <li>member2 | 박현빈</li>
            <li>member3 | 정현규</li>
          </ul>
        </div>
      </div>

      <div class="icon-section">
        <a href="https://www.instagram.com/sdhs__official/" target="_blank" class="icon-link">
          <i class="fa-brands fa-instagram"></i>
        </a>
        <a href="https://www.youtube.com/@SeoulDigitechHS" target="_blank" class="icon-link">
          <i class="fa-brands fa-youtube"></i> </a>
        <a href="./index.php" class="icon-link">
          <i class="fa fa-home"></i>
        </a>
      </div>

      <hr class="footer-divider">

      <div class="copyright">
        <p>&copy; COPYRIGHT. 2025. SDHS. OHBOBCHU</p>
      </div>
    </div>
  </footer>



  <script src="./script/index.js"></script>
</body>

</html>