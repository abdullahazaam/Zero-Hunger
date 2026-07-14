<?php
include 'db.php';

$message = "";
if (isset($_POST['register'])) {
    $role_id = intval($_POST['role_id']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password_hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    // Check duplicate email
    $checkEmail = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $checkEmail->store_result();

    if ($checkEmail->num_rows > 0) {
        $message = "<div class='alert alert-danger'>Email already registered!</div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (role_id, full_name, email, password_hash, phone, address, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("isssss", $role_id, $full_name, $email, $password_hash, $phone, $address);
        
        if ($stmt->execute()) {
            header("Location: signin.php?registered=1");
            exit();
        } else {
            $message = "<div class='alert alert-danger'>Registration failed. Try again!</div>";
        }
        $stmt->close();
    }
    $checkEmail->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Zero Hunger - Sign Up</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href=\"img/favicon.ico\" rel=\"icon\">
    <link rel=\"preconnect\" href=\"https://fonts.googleapis.com\">
    <link rel=\"preconnect\" href=\"https://fonts.gstatic.com\" crossorigin>
    <link href=\"https://fonts.googleapis.com/css2?family=Heebo:wght=400;500;600;700&display=swap\" rel=\"stylesheet\">
    <link href=\"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css\" rel=\"stylesheet\">
    <link href=\"https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css\" rel=\"stylesheet\">
    <link href=\"lib/owlcarousel/assets/owl.carousel.min.css\" rel=\"stylesheet\">
    <link href=\"lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css\" rel=\"stylesheet\" />
    <link href=\"css/bootstrap.min.css\" rel=\"stylesheet\">
    <link href=\"css/style.css\" rel=\"stylesheet\">
</head>
<body>
    <div class="container-fluid position-relative bg-white d-flex p-0">
        <div class="container-fluid">
            <div class="row h-100 align-items-center justify-content-center" style="min-height: 100vh;">
                <div class="col-12 col-sm-8 col-md-6 col-lg-5 col-xl-4">
                    <div class="bg-light rounded p-4 p-sm-5 my-4 mx-3">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h3 class="text-primary"><i class="fa fa-utensils me-2"></i>Zero Hunger</h3>
                            <h3>Sign Up</h3>
                        </div>
                        <?php echo $message; ?>
                        <form method="POST" action="">
                            <div class="form-floating mb-3">
                                <select class="form-select" name="role_id" id="roleSelect" required>
                                    <option value="2">Donor (Restaurant / Individual)</option>
                                    <option value="3">Receiver (NGO / Shelter Home)</option>
                                    <option value="4">Volunteer (Delivery Partner)</option>
                                </select>
                                <label for="roleSelect">Join As A</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="full_name" id="floatingText" placeholder="John Doe" required>
                                <label for="floatingText">Full Name / Organization</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" name="email" id="floatingInput" placeholder="name@example.com" required>
                                <label for="floatingInput">Email address</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" name="password" id="floatingPassword" placeholder="Password" required>
                                <label for="floatingPassword">Password</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="phone" id="floatingPhone" placeholder="03XXXXXXXXX" required>
                                <label for="floatingPhone">Phone Number</label>
                            </div>
                            <div class="form-floating mb-3">
                                <textarea class="form-control" name="address" id="floatingAddress" placeholder="Address" style="height: 80px;" required></textarea>
                                <label for="floatingAddress">Full Address</label>
                            </div>
                            <button type="submit" name="register" class="btn btn-primary py-3 w-100 mb-4">Sign Up</button>
                            <p class="text-center mb-0">Already have an Account? <a href="signin.php">Sign In</a></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>