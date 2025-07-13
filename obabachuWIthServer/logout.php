<?php
session_start();
session_destroy();
session_reset();

echo "<script>alert('로그아웃 되었습니다.'); window.location.href = './index.php';</script>";