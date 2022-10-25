<?php

include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:administrator_lgn.php');
}

//cfg admin register
if(isset($_POST['register'])){

   $username = $_POST['name'];
   $username = filter_var($username, FILTER_SANITIZE_STRING);
   $pass_user = sha1($_POST['pass']);
   $pass_user = filter_var($pass_user, FILTER_SANITIZE_STRING);
   $cpass_user = sha1($_POST['cpass']);
   $cpass_user = filter_var($cpass_user, FILTER_SANITIZE_STRING);

   $select_admin = $conn->prepare("SELECT * FROM `admin` WHERE name = ?");
   $select_admin->execute([$username]);

   if($select_admin->rowCount() > 0){
      $message[] = 'deja exista!';
   }else{
      if($pass_user != $cpass_user){
         $message[] = 'parola nu se potriveste!';
      }else{
         $insert_admin = $conn->prepare("INSERT INTO `admin`(name, password) VALUES(?,?)");
         $insert_admin->execute([$username, $cpass_user]);
         $message[] = 'admin inregistrat cu succes!';
      }
   }

}

//

// comenzi cfg
if(isset($_POST['update_payment'])){

   $order_id = $_POST['order_id'];
   $payment_order_status = $_POST['payment_status'];
   $payment_order_status = filter_var($payment_order_status, FILTER_SANITIZE_STRING);
   $update_payment = $conn->prepare("UPDATE `orders` SET payment_status = ? WHERE id = ?");
   $update_payment->execute([$payment_order_status, $order_id]);
   $message[] = 'Status actualizat!';

}

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   $delete_order = $conn->prepare("DELETE FROM `orders` WHERE id = ?");
   $delete_order->execute([$delete_id]);
   header('location:management.php');
}

//comenzi 

//cfg produse
if(isset($_POST['add_product'])){

   $username = $_POST['name'];
   $username = filter_var($username, FILTER_SANITIZE_STRING);
   $price = $_POST['price'];
   $price = filter_var($price, FILTER_SANITIZE_STRING);

   $image = $_FILES['image']['name'];
   $image = filter_var($image, FILTER_SANITIZE_STRING);
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'img_produse_inc/'.$image;

   $select_product = $conn->prepare("SELECT * FROM `products` WHERE name = ?");
   $select_product->execute([$username]);

   if($select_product->rowCount() > 0){
      $message[] = 'numele produsului deja exista!';
   }else{
      if($image_size > 3000000){
         $message[] = 'dimnesiunea este prea mare';
      }else{
         $insert_product = $conn->prepare("INSERT INTO `products`(name, price, image) VALUES(?,?,?)");
         $insert_product->execute([$username, $price, $image]);
         move_uploaded_file($image_tmp_name, $image_folder);
         $message[] = 'produs nou adaugat!';
      }
   }

}

if(isset($_GET['delete'])){

   $delete_id = $_GET['delete'];
   $delete_product_image = $conn->prepare("SELECT image FROM `products` WHERE id = ?");
   $delete_product_image->execute([$delete_id]);
   $fetch_delete_image = $delete_product_image->fetch(PDO::FETCH_ASSOC);
   unlink('img_produse_inc/'.$fetch_delete_image['image']);
   $delete_product = $conn->prepare("DELETE FROM `products` WHERE id = ?");
   $delete_product->execute([$delete_id]);
   $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE pid = ?");
   $delete_cart->execute([$delete_id]);
   header('location:management.php');

}
//

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <titlu>ZenGarden Management</titlu>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom admin style link  -->
   <link rel="stylesheet" href="css/admin_style.css">

</head>


<body>
<header class="header">

   <section class="flex">

      <a href="#add-produse" class="zen"><span>Zen</span>Garden</a>

      <nav class="navbar">
         <a href="#add-produse">Adauga produse</a>
         <a href="#show-produse">Produse</a>
         <a href="#panou_dsb">Comenzi</a>
         <a href="#form-container">Inregistrare admin</a>
      </nav>

   </section>

</header>
<div class="container">
  <img src="images/bg2.png" alt="" style="width:100%;">
  <div class="centered"><h1 class="heading">Website Management</h1></div>
</div>

<section class="add-produse" id ="add-produse">

   <h1 class="heading">Adauga produs</h1>

   <form action="" method="post" enctype="multipart/form-data">
      <input type="text" class="box" required maxlength="100" placeholder="nume produs" name="name">
      <input type="number" min="0" class="box" required max="9999999999" placeholder="pret" onkeypress="if(this.value.length == 10) return false;" name="price">
      <input type="file" name="image" accept="image/jpg, image/jpeg, image/png" class="box" required>
      <input type="submit" value="adauga produs" class="btn" name="add_product">
   </form>


</section>

<section class="show-produse" id="show-produse">

   <h1 class="heading">Produse adaugate</h1>

   <div class="box-cnt">

   <?php
      $select_products = $conn->prepare("SELECT * FROM `products`");
      $select_products->execute();
      if($select_products->rowCount() > 0){
         while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){ 
   ?>
   <div class="box">
      <div class="price">Lei<span><?= $fetch_products['price']; ?></span>/-</div>
      <img src="img_produse_inc/<?= $fetch_products['image']; ?>" alt="">
      <div class="name"><?= $fetch_products['name']; ?></div>
      <div class="flex-btn">
         <a href="menu_update.php?update=<?= $fetch_products['id']; ?>" class="option-btn">update</a>
         <a href="management.php?delete=<?= $fetch_products['id']; ?>" class="delete-btn" onclick="return confirm('stergere?');">sterge</a>
      </div>
   </div>
   <?php
         }
      }else{
         echo '<p class="empty">niciun produs adaugat inca!</p>';
      }
   ?>
   
   
   </div>

</section>


<section class="panou_dsb" id ="panou_dsb" >
<h1 class="heading">Total comenzi complet</h1>
   <div class="box-cnt">
   <div class="box">
   
   <?php
            $total_completes = 0;
            $select_completes = $conn->prepare("SELECT * FROM `orders` WHERE payment_status = ?");
            $select_completes->execute(['complete']);
            if($select_completes->rowCount() > 0){
               while($fetch_completes = $select_completes->fetch(PDO::FETCH_ASSOC)){
                  $total_completes += $fetch_completes['total_price'];
               }
            }
         ?>
         <h3>Lei<?= $total_completes; ?>/-</h3>
   
         <p>Venit din comenzile complet</p>
      </div>
         </div>
 <h1 class="heading">Actualizare comenzi</h1>
<div class="box-cnt">

<br>
<?php
      $select_orders = $conn->prepare("SELECT * FROM `orders`");
      $select_orders->execute();
      if($select_orders->rowCount() > 0){
         while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)){
   ?>
   <div class="box">
      <p> Data : <span><?= $fetch_orders['placed_on']; ?></span> </p>
      <p> Nume : <span><?= $fetch_orders['name']; ?></span> </p>
      <p> Telefon : <span><?= $fetch_orders['number']; ?></span> </p>
      <p> Adresa : <span><?= $fetch_orders['address']; ?></span> </p>
      <p> Produse : <span><?= $fetch_orders['total_products']; ?></span> </p>
      <p> Total : <span><?= $fetch_orders['total_price']; ?></span> </p>
      <p> Plata : <span><?= $fetch_orders['method']; ?></span> </p>
      <form action="" method="post">
         <input type="hidden" name="order_id" value="<?= $fetch_orders['id']; ?>">
         <select name="payment_status" class="select">
            <option selected disabled><?= $fetch_orders['payment_status']; ?></option>
            <option value="incompleta">Asteptare</option>
            <option value="completa">Finalizata</option>
         </select>
        <div class="flex-btn">
         <input type="submit" value="update" class="option-btn" name="update_payment">
         <a href="admin_orders.php?delete=<?= $fetch_orders['id']; ?>" class="delete-btn" onclick="return confirm('sterge?');">sterge</a>
        </div>
      </form>
   </div>
   <?php
         }
      }else{
         echo '<p class="empty">nu exista comenzi plasate inca!</p>';
      }
   ?>

</div>


</section>



<h1 class="heading">Inregistrare admin</h1>
<section class="form-container" id="form-container">

   <form action="" plata="post">
      <h3>Inregistreaza admin</h3>
      <input type="text" name="name" required placeholder="name utilizator" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="pass" required placeholder="parola" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="cpass" required placeholder="confirma parola" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="submit" value="Intregistreaza" class="btn" name="register">
   </form>

</section>

</body>
</html>