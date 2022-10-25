<?php

include 'config.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

if(isset($_POST['register'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $pass = sha1($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);
   $cpass = sha1($_POST['cpass'] );
   //sha1 cripteaza parola cu algoritmul SHA1
   //folosim functia php sha1 pentru a hashura parola utilizatorului din motive de siguranta/securitate
   $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

   $select_user = $conn->prepare("SELECT * FROM `user` WHERE name = ? AND email = ?");
   $select_user->execute([$name, $email]);

   if($select_user->rowCount() > 0){
      $message[] = 'numele exista deja!';
   }else{
      if($pass != $cpass){
         $message[] = 'confirma parola!';
      }else{
         $insert_user = $conn->prepare("INSERT INTO `user`(name, email, password) VALUES(?,?,?)");
         $insert_user->execute([$name, $email, $cpass]);
         $message[] = 'inregistrat cu succes!';
      }
   }

}

if(isset($_POST['update_qty'])){
   $cart_id = $_POST['cart_id'];
   $qty = $_POST['qty'];
   $qty = filter_var($qty, FILTER_SANITIZE_STRING);
   $update_qty = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ?");
   $update_qty->execute([$qty, $cart_id]);
   $message[] = 'cos actualizat!';
}

if(isset($_GET['delete_cart_item'])){
   $delete_cart_id = $_GET['delete_cart_item'];
   $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE id = ?");
   $delete_cart_item->execute([$delete_cart_id]);
   header('location:index.php');
}

if(isset($_GET['logout'])){
   session_unset();
   session_destroy();
   header('location:index.php');
}

if(isset($_POST['add_to_cart'])){

   if($user_id == ''){
      $message[] = 'logheaza-te!';
   }else{

      $pid = $_POST['pid'];
      $name = $_POST['name'];
      $price = $_POST['price'];
      $image = $_POST['image'];
      $qty = $_POST['qty'];
      $qty = filter_var($qty, FILTER_SANITIZE_STRING);

      $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ? AND name = ?");
      $select_cart->execute([$user_id, $name]);

      if($select_cart->rowCount() > 0){
         $message[] = 'este deja adaugat in cos';
      }else{
         $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
         $insert_cart->execute([$user_id, $pid, $name, $price, $qty, $image]);
         $message[] = 'Produs adaugat!';
      }

   }

}

if(isset($_POST['order'])){

   if($user_id == ''){
      $message[] = 'Logheaza-te!';
   }else{
      $name = $_POST['name'];
      $name = filter_var($name, FILTER_SANITIZE_STRING);
      $number = $_POST['number'];
      $number = filter_var($number, FILTER_SANITIZE_STRING);
      $address = 'oras'.$_POST['oras'].', '.$_POST['strada'];
      $address = filter_var($address, FILTER_SANITIZE_STRING);
      $method = $_POST['method'];
      $method = filter_var($method, FILTER_SANITIZE_STRING);
      $total_price = $_POST['total_price'];
      $total_products = $_POST['total_products'];

      $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
      $select_cart->execute([$user_id]);

      if($select_cart->rowCount() > 0){
         $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, method, address, total_products, total_price) VALUES(?,?,?,?,?,?,?)");
         $insert_order->execute([$user_id, $name, $number, $method, $address, $total_products, $total_price]);
         $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
         $delete_cart->execute([$user_id]);
         $message[] = 'comanda plasata cu succes!';
      }else{
         $message[] = 'cosul dvs. este gol!';
      }
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <titlu>Restaurant Zen Garden</titlu>
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
	<script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>
   <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
	<link rel="preconnect" href="https://fonts.gstatic.com">
	<script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>
  <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,100;1,200;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
  <script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" integrity="sha512-iBBXm8fW90+nuLcSKlbmrPcLa0OT92xO1BIsZ+ywDWZCvqsWgccV3gFoRBv0z+8dLJgyAHIhR35VZc2oM/gI1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>

<?php
   if(isset($message)){
      foreach($message as $message){
         echo '
         <div class="message">
            <span>'.$message.'</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
         </div>
         ';
      }
   }
?>

<!-- header section starts  -->

<header class="header">

   <section class="flex">

      <a href="#home" class="zen"><span>Zen</span>Garden</a>

      <nav class="navbar">
         <a href="#home">Acasa</a>
         <a href="#about">Despre</a>
         <a href="#menu">Meniu</a>
         <a href="#comanda">Comanda</a>
         <a href="#ask">Intrebari</a>
         <a href="#login">Logare</a>
      </nav>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars">Meniu</div>
         
         <?php
            $count_cos_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $count_cos_items->execute([$user_id]);
            $total_cos_items = $count_cos_items->rowCount();
         ?>
         <div id="cos-btn" class="fas fa-shopping-cart"><span>(<?= $total_cos_items; ?>)</span>Cos</div>
         <div id="user-btn" class="fas fa-user">Cont</div>
      </div>

   </section>

</header>

<!-- header section ends -->

<div class="ut-cont">

   <section>

      <div id="cls-cont"><span>Inchide</span></div>

      <div class="user">
      <?php
            $select_user = $conn->prepare("SELECT * FROM `user` WHERE id = ?");
            $select_user->execute([$user_id]);
            if($select_user->rowCount() > 0){
               while($fetch_user = $select_user->fetch(PDO::FETCH_ASSOC)){
                  echo '<p>Salutare ! <span>'.$fetch_user['name'].'</span></p>';
                  echo '<a href="index.php?logout" class="btn">logout</a>';
               }
            }else{
               echo '<p><span>Logare!</span></p>';
            }
         ?>
      </div>

      <div class="display-orders">
         <?php
            $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $select_cart->execute([$user_id]);
            if($select_cart->rowCount() > 0){
               while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
                  echo '<p>'.$fetch_cart['name'].' <span>('.$fetch_cart['price'].' x '.$fetch_cart['quantity'].')</span></p>';
               }
            }else{
               echo '<p><span>cosul dvs. este gol!</span></p>';
            }
         ?>
      </div>


   </section>

</div>



<div class="m-cmz">

   <section>

      <div id="cls-cmz"><span>Inchide</span></div>

      <h3 class="titlu"> Comenzile mele </h3>

      <?php
         $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ?");
         $select_orders->execute([$user_id]);
         if($select_orders->rowCount() > 0){
            while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)){   
      ?>
      <div class="box">
         <p> Data : <span><?= $fetch_orders['placed_on']; ?></span> </p>
         <p> Nume : <span><?= $fetch_orders['name']; ?></span> </p>
         <p> Telefon : <span><?= $fetch_orders['number']; ?></span> </p>
         <p> Adresa : <span><?= $fetch_orders['address']; ?></span> </p>
         <p> Plata : <span><?= $fetch_orders['method']; ?></span> </p>
         <p> Produse : <span><?= $fetch_orders['total_products']; ?></span> </p>
         <p> Pret : <span>Lei<?= $fetch_orders['total_price']; ?>/-</span> </p>
        
      </div>
      <?php
         }
      }else{
         echo '<p class="empty">nu aveti nicio comanda inca!</p>';
      }
      ?>


   </section>

</div>

<div class="shp-cos">

   <section>

      <div id="cls-cos"><span>Inchide</span></div>

      <?php
         $grand_total = 0;
         $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
         $select_cart->execute([$user_id]);
         if($select_cart->rowCount() > 0){
            while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
              $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']);
              $grand_total += $sub_total; 
      ?>
      <div class="box">
         <a href="index.php?delete_cart_item=<?= $fetch_cart['id']; ?>" class="fas fa-times" onclick="return confirm('sterge acest produs?');"></a>
         <img src="img_produse_inc/<?= $fetch_cart['image']; ?>" alt="">
         <div class="content">
          <p> <?= $fetch_cart['name']; ?> <span>(<?= $fetch_cart['price']; ?> x <?= $fetch_cart['quantity']; ?>)</span></p>
          <form action="" method="post">
             <input type="hidden" name="cart_id" value="<?= $fetch_cart['id']; ?>">
             <input type="number" name="qty" class="qty" min="1" max="99" value="<?= $fetch_cart['quantity']; ?>" onkeypress="if(this.value.length == 2) return false;">
               <button type="submit" class='fas fa-cart-plus' style='font-size:36px; padding:2px; background-color: #333; color: #e7b73c' name="update_qty"></button>
          </form>
         </div>
      </div>
      <?php
         }
      }else{
         echo '<p class="empty"><span>cosul dvs. este gol!</span></p>';
      }
      ?>

      <div class="cos-total"> Total : <span>Lei<?= $grand_total; ?>/-</span></div>

      <a href="#comanda" class="btn">Comanda</a>

   </section>

</div>

<div class="home-bg">

   <section class="home" id="home">

      <div class="sld-cnt">
      
         <div class="slide active">
            <div class="image">
            <img src="images/bg2.png" alt="">
            </div>
            <div class="content">
               <h3>Fiecare mușcătură e specială.</h3>
               <div class="fas fa-angle-left" onclick="prev()"></div>
               <div class="fas fa-angle-right" onclick="next()"></div>
            </div>
         </div>

         <div class="slide">
            <div class="image">
               <img src="images/home-img-1.png" alt="">
            </div>
            <div class="content">
               <h3>Bucatarie internationala</h3>
               <div class="fas fa-angle-left" onclick="prev()"></div>
               <div class="fas fa-angle-right" onclick="next()"></div>
            </div>
         </div> 
         <div class="slide">
            <div class="image">
               <img src="images/home-img-2.png" alt="">
            </div>
            <div class="content">
               <h3>Bucataria mexicana</h3>
               <div class="fas fa-angle-left" onclick="prev()"></div>
               <div class="fas fa-angle-right" onclick="next()"></div>
            </div>
         </div>

         <div class="slide">
            <div class="image">
               <img src="images/home-img-3.png" alt="">
            </div>
            <div class="content">
               <h3>Bucataria frantuzeasca</h3>
               <div class="fas fa-angle-left" onclick="prev()"></div>
               <div class="fas fa-angle-right" onclick="next()"></div>
            </div>
         </div>

      </div>

   </section>

</div>

<!-- about section starts  -->

<section class="about" id="about">

   <h1 class="heading">Despre noi</h1>

   <div class="box-cnt">

      <div class="box">
      
         <h3>Gatim din pasiune</h3>
         <p>Meniul restaurantelor Zen Garden este unul deosebit de versatil, o bucatarie citadina, urbana, reinterpretata, plina de gust si savoare!<br><br></p>
         <a href="#menu" class="btn">meniul nostru</a>
      </div>

      <div class="box">
    
         <h3>Livrare rapida</h3>
         <p>Vrei ceva bun?Comanda online! Restaurant Zen Garden, cu un meniu international generos si peste 20 ani experienta, iti aduce acasa, in maxima siguranta, preparate cu gust.
</p>
         <a href="#about" class="btn">despre noi</a>
      </div>

      <div class="box">
        
         <h3>Viziteaza-ne in locatie</h3>
         <p>Fie ca aniversati un eveniment deosebit din viata dumneavoastra sau organizari o cina de afaceri, Restaurantul Zen Garden este locatia fine dining care nu va va dezamagi.<br><br></p>
         <a href="#menu" class="btn">locatia noastra</a>
      </div>

   </div>
   	<!----abt section start---->
	<section class="abt">
		<div class="mn">
			<img src="images/chef-img.jpg">
			<div class="abt-text">
				<h2>Alex Popescu</h2>
				<h5>Bucatar<span> Sef</span></h5>
				<p>Alex Popescu, bucătar de rename internațional, cu mai multe stele Michelin, a deschis o serie de restaurante de succes în întreaga lume, din Marea Britanie și Franța până în Singapore și Statele Unite. Alex a devenit, de asemenea, o vedetă a micului ecran atât în Marea Britanie, cât și la nivel internațional, cu emisiuni precum Kitchen Nightmares, Hell's Kitchen, Hotel Hell și MasterChef US. In prezent este bucatarul sef al restaurantului Zen Garden.</p>
				<button class="btns" type="button">Afla mai multe</button>
			</div>
		</div>
	</section>
	<!-----service section start----------->
	<div class="service">
		<div class="titlu">
			<h2>Serviciile noastre</h2>
		</div>

		<div class="box_s">
			<div class="card">
				<i class='fas fa-vihara'></i>
				<h5>Restaurantul</h5>
				<div class="pra">
					<p>Restaurantul nostru este deservit de bucătari de primă clasă din bucătăria internațională, care combină rețete tradiționale vechi cu rețete moderne, în plus, restaurantul nostru este dotat cu cele mai noi și mai moderne ustensile de bucătărie.</p>

					<p style="text-align: center;">
						<a class="button" href="#about">Mai multe</a>
					</p>
				</div>
			</div>

			<div class="card">
				<i class='fas fa-drumstick-bite'></i>
				<h5>Meniul</h5>
				<div class="pra">
					<p>Bucătarii restaurantului sunt instruiți și specializați în toate țările din lume în prepararea oricărei rețete tradiționale internaționale aproape de perfecțiune. Toți bucătarii noștri au mulți ani de experiență în restaurante internaționale.<br></p>
					<p style="text-align: center;">
						<a class="button" href="#menu">Mai multe</a>
					</p>
				</div>
			</div>

			<div class="card">
				<i class='fas fa-shipping-fast'></i>
				<h5>Livrarea</h5>
				<div class="pra">
					<p>Restaurantul nostru livrează în cel mai scurt timp posibil după pregătirea produselor comandate și oferă garanția calității și prospețimii produselor. <br><br> <br> <br></p>

					<p style="text-align: center;">
						<a class="button" href="#about">Mai multe</a>
					</p>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- about section ends -->
<!-- MAIN (Center website) -->
<div class="main">

<h1 style="color:#e7b73c;">Unde ne gasesti?</h1>
<hr>


<div id="myBtnContainer">
  <button class="btn_a active" onclick="filterSelection('all')"> Toate locatiile</button>
  <button class="btn_a" onclick="filterSelection('craiova')"> Craiova</button>
  <button class="btn_a" onclick="filterSelection('bucuresti')"> Bucuresti</button>
  <button class="btn_a" onclick="filterSelection('cluj')"> Cluj</button>
</div>

<!-- Portfolio Gallery Grid -->
<div class="row">
  <div class="column craiova">
    <div class="cnt">
      <img src="https://images.unsplash.com/photo-1592861956120-e524fc739696?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxzZWFyY2h8OHx8cmVzdGF1cmFudHxlbnwwfDB8MHx8&auto=format&fit=crop&w=500&q=60" alt="" style="width:100%">
      <h4>Craiova</h4>
      <p>Strada, Numar 5</p>
    </div>
  </div>
  <div class="column craiova">
    <div class="cnt">
    <img src="https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=870&q=80" alt="" style="width:100%">
      <h4>Craiova</h4>
      <p>Lorem ipsum dolor..</p>
    </div>
  </div>
  <div class="column craiova">
    <div class="cnt">
    <img src="https://images.unsplash.com/photo-1414235077428-338989a2e8c0?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxzZWFyY2h8Mnx8cmVzdGF1cmFudHxlbnwwfDB8MHx8&auto=format&fit=crop&w=500&q=60" alt="" style="width:100%">
      <h4>Craiova</h4>
      <p>Lorem ipsum dolor..</p>
    </div>
  </div>
  
  <div class="column bucuresti">
    <div class="cnt">
      <img src="https://images.unsplash.com/photo-1552566626-52f8b828add9?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxzZWFyY2h8NHx8cmVzdGF1cmFudHxlbnwwfDB8MHx8&auto=format&fit=crop&w=500&q=60" alt="" style="width:100%">
      <h4>Bucuresti</h4>
      <p>Strada, Numar </p>
    </div>
  </div>
  <div class="column bucuresti">
    <div class="cnt">
    <img src="https://images.unsplash.com/photo-1579027989536-b7b1f875659b?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxzZWFyY2h8Mjh8fHJlc3RhdXJhbnR8ZW58MHwwfDB8fA%3D%3D&auto=format&fit=crop&w=500&q=60" alt="" style="width:100%">
      <h4>Bucuresti</h4>
      <p>Lorem ipsum dolor..</p>
    </div>
  </div>
  <div class="column bucuresti">
    <div class="cnt">
    <img src="https://images.unsplash.com/photo-1533777857889-4be7c70b33f7?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxzZWFyY2h8N3x8cmVzdGF1cmFudHxlbnwwfDB8MHx8&auto=format&fit=crop&w=500&q=60" alt="" style="width:100%">
      <h4>Bucuresti</h4>
      <p>Lorem ipsum dolor..</p>
    </div>
  </div>

  <div class="column cluj">
    <div class="cnt">
      <img src="https://images.unsplash.com/photo-1551632436-cbf8dd35adfa?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxzZWFyY2h8MTB8fHJlc3RhdXJhbnR8ZW58MHwwfDB8fA%3D%3D&auto=format&fit=crop&w=500&q=60" alt="" style="width:100%">
      <h4>Cluj</h4>
      <p>Strada, Numar</p>
    </div>
  </div>
  <div class="column cluj">
    <div class="cnt">
    <img src="https://images.unsplash.com/photo-1482275548304-a58859dc31b7?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxzZWFyY2h8NjB8fHJlc3RhdXJhbnR8ZW58MHwwfDB8fA%3D%3D&auto=format&fit=crop&w=500&q=60" alt="" style="width:100%">
      <h4>Cluj</h4>
      <p>Lorem ipsum dolor..</p>
    </div>
  </div>
  <div class="column cluj">
    <div class="cnt">
    <img src="https://images.unsplash.com/photo-1531973819741-e27a5ae2cc7b?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxzZWFyY2h8NjZ8fHJlc3RhdXJhbnR8ZW58MHwwfDB8fA%3D%3D&auto=format&fit=crop&w=500&q=60" alt="" style="width:100%">
      <h4>Cluj</h4>
      <p>Lorem ipsum dolor..</p>
    </div>
  </div>
<!-- END GRID -->
</div>

<!-- END MAIN -->
</div>
<!-- menu section starts  -->

<section id="menu" class="menu">

   <h1 class="heading">Meniul Restaurantului</h1>

   <div class="box-cnt">

   <?php
         $select_products = $conn->prepare("SELECT * FROM `products`");
         $select_products->execute();
         if($select_products->rowCount() > 0){
            while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){    
      ?>
      <div class="box">
         <div class="price">Lei<?= $fetch_products['price'] ?>/-</div>
         <img src="img_produse_inc/<?= $fetch_products['image'] ?>" alt="">
         <div class="name"><?= $fetch_products['name'] ?></div>
         <form action="" method="post">
            <input type="hidden" name="pid" value="<?= $fetch_products['id'] ?>">
            <input type="hidden" name="name" value="<?= $fetch_products['name'] ?>">
            <input type="hidden" name="price" value="<?= $fetch_products['price'] ?>">
            <input type="hidden" name="image" value="<?= $fetch_products['image'] ?>">
            <input type="number" name="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="1">
            <input type="submit" class="btn" name="add_to_cart" value="adauga">
         </form>
      </div>
      <?php
         }
      }else{
         echo '<p class="empty">cosul dvs. este gol!</p>';
      }
      ?>

   </div>

</section>

<!-- menu section ends -->
<!-- login --->


   <section id="login" class="login">

      <div class="center_login">
      <div class="flex">
      <form action="user_login.php" method="post">
            <h3>Logare</h3>
            <input type="email" name="email" required class="bx" placeholder="introduceti email dvs." maxlength="50">
            <input type="password" name="pass" required class="bx" placeholder="introduceti parola dvs." maxlength="20">
            <input type="submit" value="Logare" name="login" class="btn">
         </form>
      </div>
      <br><br><br>

      <div class="flex">
      <form action="" method="post">
            <h3>Inregistrare</h3>
            <input type="text" name="name" oninput="this.value = this.value.replace(/\s/g, '')" required class="bx" placeholder="introduceti numele de utilizartor" maxlength="20">
            <input type="email" name="email" required class="bx" placeholder="introduceti emailul" maxlength="50">
            <input type="password" name="pass" required class="bx" placeholder="introduceti o parola" maxlength="20" oninput="this.value = this.value.replace(/\s/g, '')">
            <input type="password" name="cpass" required class="bx" placeholder="confirma parola" maxlength="20" oninput="this.value = this.value.replace(/\s/g, '')">
            <input type="submit" value="Inregistrare" name="register" class="btn">
         </form>
      </div>
      </div>

   </section>


<!-- end login  -->

<!-- comanda section starts  -->

<section class="comanda" id="comanda">

   <h1 class="heading">Comanda acum</h1>

   <form action="" method="post">

   <div class="display-comenzi">

   <?php
         $grand_total = 0;
         $cart_item[] = '';
         $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
         $select_cart->execute([$user_id]);
         if($select_cart->rowCount() > 0){
            while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
              $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']);
              $grand_total += $sub_total; 
              $cart_item[] = $fetch_cart['name'].' ( '.$fetch_cart['price'].' x '.$fetch_cart['quantity'].' ) - ';
              $total_products = implode($cart_item);
              echo '<p>'.$fetch_cart['name'].' <span>('.$fetch_cart['price'].' x '.$fetch_cart['quantity'].')</span></p>';
            }
         }else{
            echo '<p class="empty"><span>cosul dvs este gol!</span></p>';
         }
      ?>

   </div>


      <input type="hidden" name="total_products" value="<?= $total_products; ?>">
      <input type="hidden" name="total_price" value="<?= $grand_total; ?>">

      <div class="flex">
         <div class="inputBox">
            <span>Numele dvs. :</span>
            <input type="text" name="name" class="box" required placeholder="Numele dvs" maxlength="20">
         </div>
         <div class="inputBox">
            <span>Telefon :</span>
            <input type="number" name="number" class="box" required placeholder="numarul telefon" min="0" max="9999999999" onkeypress="if(this.value.length == 10) return false;">
         </div>
         <div class="inputBox">
            <span>Plata</span>
            <select name="method" class="box">
               <option value="cash on delivery">cash</option>
               <option value="credit card">card</option>

            </select>
         </div>
         <div class="inputBox">
            <span>Oras :</span>
            <input type="text" name="oras" class="box" required placeholder="oras" maxlength="50">
         </div>
         <div class="inputBox">
            <span>Strada :</span>
            <input type="text" name="strada" class="box" required placeholder="Strada" maxlength="50">
         </div>
      </div>

      <input type="submit" value="Comanda acum" class="btn" name="order">

   </form>

</section>

<!-- comanda section ends -->

<!-- ask section starts  -->

<section class="ask" id="ask">

   <h1 class="heading">Intrebari?</h1>

   <div class="intrb-container">

      <div class="intrb active">
         <div class="intrb-heading">
            <span>Cat de repede ajunge comanda?</span>
            <i class="fas fa-angle-down"></i>
         </div>
         <p class="despre-content">
         Restaurant Zen Garden, cu un meniu international generos si peste 20 ani experienta, iti aduce acasa, in maxima siguranta, preparate cu gust.
         </p>
      </div>

      <div class="intrb">
         <div class="intrb-heading">
            <span>Ce este ZenGarden?</span>
            <i class="fas fa-angle-down"></i>
         </div>
         <p class="despre-content">
         Restaurantul nostru este deservit de bucătari de primă clasă din bucătăria internațională, care combină rețete tradiționale vechi cu rețete moderne, în plus, restaurantul nostru este dotat cu cele mai noi și mai moderne ustensile de bucătărie.
         </p>
      </div>

      <div class="intrb">
         <div class="intrb-heading">
            <span>Ce contine meniul Zen Garden?</span>
            <i class="fas fa-angle-down"></i>
         </div>
         <p class="despre-content">
         Restaurantul nostru este deservit de bucătari de primă clasă din bucătăria internațională, care combină rețete tradiționale vechi cu rețete moderne, în plus, restaurantul nostru este dotat cu cele mai noi și mai moderne ustensile de bucătărie.
         </p>
      </div>

      <div class="intrb">
         <div class="intrb-heading">
            <span>Bucatarii ZenGarden</span>
            <i class="fas fa-angle-down"></i>
         </div>
         <p class="despre-content">
         Restaurantul nostru este deservit de bucătari de primă clasă din bucătăria internațională, care combină rețete tradiționale vechi cu rețete moderne, în plus, restaurantul nostru este dotat cu cele mai noi și mai moderne ustensile de bucătărie.
         </p>
      </div>


      <div class="intrb">
         <div class="intrb-heading">
            <span>Unde se afla restaurantele Zen Garden</span>
            <i class="fas fa-angle-down"></i>
         </div>
         <p class="despre-content">
        Craiova, Bucuresti, Cluj. Fie ca aniversati un eveniment deosebit din viata dumneavoastra sau organizari o cina de afaceri, Restaurantul Zen Garden este locatia fine dining care nu va va dezamagi.
         </p>
      </div>

   </div>

</section>

<!-- ask section ends -->
	<!------Contact Me------>
	<div class="contact-me">
		<p>Pentru orice tip de evenimente, va rugam, contactati-ne! 0351 345 678</p>
		<a class="button-two" href="#about">Contactaza-ne</a>
	</div>

	<!------footer start--------->
	<footer>
		<p>Proiect Licenta 2022</p>
		<p>Facultatea de Automatica, Calculatoare si Electronica din Craiova. 2022</p>
		<div class="social">
			<a href="#"><i class="fab fa-facebook-f"></i></a>
			<a href="#"><i class="fab fa-instagram"></i></a>
			<a href="#"><i class="fab fa-dribbble"></i></a>
		</div>
		<p class="end">Zen Garden Restaurant</p>
	</footer>
<!-- footer_section_section section starts  -->

<section class="footer_section">

   <div class="box-cnt">

      <div class="box">
         <h3>Telefon</h3>
         <p>0351 345 678</p>
      </div>

      <div class="box">
         <h3>Adresa</h3>
         <p>Craiova</p>
      </div>

      <div class="box">
         <h3>Program</h3>
         <p>07:30 - 24:00</p>
      </div>

      <div class="box">
         <h3>Email</h3>
         <p>ZenGarden@gmail.com</p>
      </div>

   </div>

</section>

<!-- footer_section_section section ends -->
<div class="loader-container">
    <img src="images/loader.gif" alt="">
</div>

<script src="js/script.js"></script>

</body>
</html>