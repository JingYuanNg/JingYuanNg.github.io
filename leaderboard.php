<!DOCTYPE html>

<?php
    //start session 
    session_start(); 
    require_once './validation.php';
 
    if(empty($_SESSION["pName"]))
    {
        $location = "login.php";
        echo "<script type='text/JavaScript'>alert('Please log in to continue');window.location='$location'</script>"; 
    } 
?> 
<html>
<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="img/icon.png" type="image/png" sizes="16x16">
    <title>INSHIELD | Leaderboard</title>
    <link href="css/styles.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Strait">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
</head>
<style>   

    .display-top
    {
        padding-top: 90px;
    }

    .display-inside
    {
        padding-top: 50px;
    }

    .txt
    {
        font-family: "Strait"; 
        color: #000000;
    }
 
    .btn-design
    { 
        border-color: #000000 !important;
        background-color: #F5F5DC !important;
    }
     
    .text-center a:hover
    {
        text-decoration: underline;
        color: #365194;
    } 
</style>

<body>
    <?php 
        
    include './headerFooterClient.php';  

    //select * from players - ID, email, points 
    //decrypt points 
    //store in array according to ID 
    //arsort() - sort array descending according to the value in the array 
    //display top 5  

    $cipher = 'AES-128-CBC';
    $key = 'thebestsecretkey';

    //connect db 
    $con = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME); 

    //SQL statement players
    $sql = "SELECT * from players"; 

    //get result from sql 
    $result = $con -> query($sql); 

    $playerID_i = 0; 
    //get data from both side 
    while($row = $result -> fetch_object())
    {
        //id 
        $playerID = $row -> playerID;

        //iv 
        $iv = hex2bin($row -> iv); 

        //email 
        $email = $row -> email;

        //points  
        $points_bin = hex2bin($row -> points); 
        $points = openssl_decrypt($points_bin, $cipher, $key, OPENSSL_RAW_DATA, $iv); 
    
        //store into array 
        $rank[$playerID] = $points; 
    }

    //arsort() - sort array descending according to the value in the array
    arsort($rank); 

    $leaderboard_position = 1; 

    foreach ($rank as $playerID => $points) 
    {  
        $con = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME); 
        $sql = "SELECT * FROM players WHERE playerID = '$playerID'";
        $result = $con -> query($sql); 

        if($row = $result -> fetch_object())
        {
            $email = $row -> email;

            $iv = hex2bin($row -> iv);

            //add email to the array
            $rank[$playerID] = array( 
                'points' => $points,
                'email' => $email, 
                'leaderboard_position' => $leaderboard_position
                );

            //encrypt_leaderboard_position
            $encrypted_leaderboard_position = openssl_encrypt($leaderboard_position, $cipher, $key, OPENSSL_RAW_DATA, $iv);
            //encrypted_leaderboard_position_hex 
            $encrypted_leaderboard_position_hex = bin2hex($encrypted_leaderboard_position);
            
            $con =  new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            $sql = "UPDATE players SET leaderboard_position = ? WHERE email = ?";
            $stmt = $con ->prepare($sql);
            $stmt -> bind_param('ss', $encrypted_leaderboard_position_hex, $email);

            if($stmt -> execute())
            {
                //update successful 
                /* echo '$email: ' . $email . '<br/>';
                echo '$leaderboard_position: ' . $leaderboard_position . '<br/>';
                echo '$encrypted_leaderboard_position_hex: ' . $encrypted_leaderboard_position_hex . '<br/><br/>'; */
            }
            else 
            {
                echo 'Uh-oh'. '<br/>'; 
            } 
            $leaderboard_position++; 
        } 
    } 
    
    $con -> close();
    ?>

    <div class="container mt-5 display-top">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h1 class="text-center txt">Leaderboard</h1>
                
                <table class="bg-white rounded shadow table-bordered table-responsive-sm mb-5" >  
                    <thead>
                    <tr>
                        <th class="p-2"><label for="rank" class="txt">Rank</label></th>
                        <th class="ps-2 w-100"><label for="user" class="txt">User</label></th>
                        <th class="pt-2 pb-2 ps-2 pe-5"><label for="points" class="txt">Points</label></th>
                    </tr>
                    </thead>
                    <?php 

                        $counter = 0;

                        foreach($rank as $playerID => $values)
                        {
                            if($counter < 5)
                            {
                                // echo "PlayerID: $playerID, Email: {$values['email']}, Points: {$values['points']}, Leaderboard: {$values['leaderboard_position']}<br/>";
                                //display
                                echo '<tr>';
                                echo '<td class="p-2"><label for="rank" class="txt">'.$values['leaderboard_position'].'</label></td>';
                                echo '<td class="ps-2 w-100"><label for="user" class="txt">'.$values['email'].'</label></td>';
                                echo '<td class="pt-2 pb-2 ps-2 pe-5"><label for="points" class="txt">'.$values['points'].'</label></td>';
                                echo '</tr>';
                                $counter++;
                            }
                            else
                            {
                                break;
                            }
                        }
                    ?> 

                    
                </table> 
 
                 
            </div>
        </div> 
    </div>
    <!-- <input type="button" value="Logout" name="logout" class="profile-btn" onclick="location = 'logout.php'; alert('You have successfully been logout!');"/> -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script> 
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.bundle.min.js"></script> -->
</body>
</html> 