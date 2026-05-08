<?php
require "../config/csrf.php";
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | topolter Messenger</title>
<link rel="icon" href="../logo.png">
<style>



@font-face{
    font-family: 'Vazirmatn';
    src: url('../Vazir.ttf') format('truetype');
    font-weight: normal;
    font-style: normal;
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:tahoma;
    font-family: 'Vazirmatn', Tahoma, sans-serif;
}
body{
    background:linear-gradient(135deg,#3b82f6,#6366f1);
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:20px;
}

/* Card */
.card{
    background:#fff;
    width:100%;
    max-width:400px;
    padding:30px 25px;
    border-radius:15px;
    box-shadow:0 10px 30px rgba(0,0,0,0.2);
    animation:fadeIn 0.4s ease;
}

/* Animation */
@keyframes fadeIn{
    from{opacity:0;transform:translateY(20px);}
    to{opacity:1;transform:translateY(0);}
}

/* Title */
.title{
    text-align:center;
    font-size:22px;
    margin-bottom:25px;
    font-weight:bold;
}

/* Input */
.input{
    width:100%;
    padding:12px;
    margin-bottom:15px;
    border:1px solid #ccc;
    border-radius:10px;
    transition:0.2s;
    font-size:14px;
}

.input:focus{
    border-color:#3b82f6;
    outline:none;
    box-shadow:0 0 0 2px rgba(59,130,246,0.2);
}

/* Button */
.btn{
    width:100%;
    padding:12px;
    background:#3b82f6;
    color:#fff;
    border:none;
    border-radius:10px;
    cursor:pointer;
    font-size:15px;
    transition:0.2s;
}

.btn:hover{
    background:#2563eb;
}

/* Link */
.footer{
    text-align:center;
    margin-top:15px;
    font-size:14px;
}

.footer a{
    color:#3b82f6;
    text-decoration:none;
    font-weight:bold;
}

.footer a:hover{
    text-decoration:underline;
}
</style>
</head>

<body>

<div class="card">
  
  <h2 class="title">Login</h2>

  <form id="loginForm" method="POST">
    <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">

    <input 
      type="text" 
      name="username" 
      placeholder="Username" 
      class="input"
    >

    <input 
      type="password" 
      name="password" 
      placeholder="Password" 
      class="input"
    >

    <button 
      type="submit" 
      class="btn"
    >
      Login
    </button>
  </form>

  <p class="footer">
    Don't have an account?
    <a href="register.php" class="text-blue-600 font-medium">Register</a>
  </p>

</div>

<script>
const form = document.getElementById("loginForm");
form.addEventListener("submit", e=>{
    e.preventDefault();
    const data = new FormData(form);
    fetch("../api/login.php",{method:"POST",body:data})
    .then(r=>r.json())
    .then(res=>{
        if(res.status==="ok") location.href="index.php";
        else alert(res.error);
    })
});
</script>

</body>
</html>