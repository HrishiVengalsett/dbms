<?php
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($message)) {
        $error = 'Please fill in all fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        // In a real application, you would send an email here
        $success = 'Thank you for your message! We will get back to you soon.';
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="text-center mb-4 text-warning">Contact Us</h1>
            <p class="text-center mb-5">Have questions or feedback? We'd love to hear from you!</p>
            
            <div class="card shadow-sm p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php else: ?>
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="name" class="form-label">Your Name</label>
                            <input type="text" class="form-control" id="name" name="name" required
                                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                            <div class="invalid-feedback">Please enter your name</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            <div class="invalid-feedback">Please enter a valid email</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Your Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required><?= 
                                htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                            <div class="invalid-feedback">Please enter your message</div>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-warning px-4">Send Message</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
            
            <div class="row mt-5">
                <div class="col-md-4 text-center mb-4">
                    <div class="p-3 border rounded">
                        <i class="bi bi-envelope-fill text-warning fs-3 mb-2"></i>
                        <h5>Email Us</h5>
                        <p class="mb-0">support@recipehub.com</p>
                    </div>
                </div>
                
                <div class="col-md-4 text-center mb-4">
                    <div class="p-3 border rounded">
                        <i class="bi bi-telephone-fill text-warning fs-3 mb-2"></i>
                        <h5>Call Us</h5>
                        <p class="mb-0">+1 (555) 123-4567</p>
                    </div>
                </div>
                
                <div class="col-md-4 text-center mb-4">
                    <div class="p-3 border rounded">
                        <i class="bi bi-geo-alt-fill text-warning fs-3 mb-2"></i>
                        <h5>Visit Us</h5>
                        <p class="mb-0">123 Foodie Street, Culinary City</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>