<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient DBMS - Database Setup</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .setup-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }
        .setup-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .setup-icon {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 20px;
        }
        .btn-setup {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            color: white;
        }
        .btn-setup:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="setup-container">
                    <div class="setup-header">
                        <div class="setup-icon">
                            <i class="fa fa-database"></i>
                        </div>
                        <h2>Patient DBMS Setup</h2>
                        <p class="text-muted">Database Setup & Configuration</p>
                    </div>

                    <div class="alert alert-info">
                        <h5><i class="fa fa-info-circle"></i> Setup Instructions</h5>
                        <ol>
                            <li>Make sure XAMPP is running (Apache + MySQL)</li>
                            <li>Click the "Setup Database" button below</li>
                            <li>Wait for the setup to complete</li>
                            <li>Use the default login credentials to access the system</li>
                        </ol>
                    </div>

                    <div class="text-center">
                        <a href="database_setup.php" class="btn btn-setup btn-lg">
                            <i class="fa fa-cog"></i> Setup Database Now
                        </a>
                    </div>

                    <hr>

                    <div class="alert alert-warning">
                        <h6><i class="fa fa-key"></i> Default Login Credentials</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Developer:</strong><br>
                                Username: <code>admin</code><br>
                                Password: <code>admin123</code>
                            </div>
                            <div class="col-md-6">
                                <strong>Doctor:</strong><br>
                                Username: <code>doctor1</code><br>
                                Password: <code>admin123</code>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <strong>Secretary:</strong><br>
                                Username: <code>secretary1</code><br>
                                Password: <code>admin123</code>
                            </div>
                            <div class="col-md-6">
                                <strong>Accountant:</strong><br>
                                Username: <code>accountant1</code><br>
                                Password: <code>admin123</code>
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <a href="login.php" class="btn btn-outline-primary">
                            <i class="fa fa-sign-in"></i> Go to Login Page
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
