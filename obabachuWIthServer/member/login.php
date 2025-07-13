<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>오밥추 - 오늘 뭐 먹지?</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css"
    integrity="sha512-10/jx2EXwxxWqCLX/hHth/vu2KY3jCF70dCQB8TSgNjbCVAC/8vai53GfMDrO2Emgwccf2pJqxct9ehpzG+MTw=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="../style/login.css" />
  <link rel="stylesheet" href="../style/loading.css" />
</head>


<main>
  <div class="register-container">
    <div class="register-box">
      <div class="register-slide">
        <div class="reshadow"></div>
      </div>

      <div class="register-box-body">
        <form action="../loginAction.php" method="post">
          <div class="login-container">
            <input type="hidden" name="action" value="login">
            <div class="input-row">
              <label for="id" class="fa fa-user"></label>
              <input type="text" id="id" name="username" placeholder="아이디" required>
            </div>
            <div class="input-row">
              <label for="password" class="fa fa-lock"></label>
              <input type="password" id="password" name="password" placeholder="비밀번호" required>
            </div>
            <button type="submit">login</button>
          </div>

        </form>

        <div class="terms-container">
          <input type="checkbox" id="agree">
          <label for="agree">Remember Me</label>
        </div>

      </div>
    </div>
  </div>
</main>

<script src="./script/script.js"></script>
</body>

</html>