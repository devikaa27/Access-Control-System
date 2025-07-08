<?php
$host = "127.0.0.1";
$port = 3307;
$user = "root";
$password = "";
$dbname = "access_control";

$conn = new mysqli($host, $user, $password, $dbname, $port);

$accountCreated = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = trim($_POST["firstname"]);
    $lastname = trim($_POST["lastname"]);
    $email = trim($_POST["email"]);
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $city = trim($_POST["city"]);
    $country = trim($_POST["country"]);
    $role = trim($_POST["role"]);

    // Check for duplicates
    $check = $conn->prepare("SELECT id FROM users WHERE username=? OR email=?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "<script>window.onload = function() { alert('❌ Username or Email already exists!'); }</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (firstname, lastname, email, username, password, city, country, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $firstname, $lastname, $email, $username, $password, $city, $country, $role);

        if ($stmt->execute()) {
            $accountCreated = true;
        } else {
            echo "<script>window.onload = function() { alert('❌ Error: " . $stmt->error . "'); }</script>";
        }

        $stmt->close();
    }

    $check->close();
}

$conn->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Account - Enosi Style</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            box-sizing: border-box;
            font-family: "Helvetica", sans-serif;
        }

        body {
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            max-width: 800px;
            margin: 60px auto;
            background: #fff;
            border: 1px solid #ccc;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.05);
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 24px;
            color: #333;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
        }

        .form-group label {
            margin-bottom: 6px;
            font-weight: bold;
        }

        .form-group input, 
        .form-group select {
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #2c7edb;
        }

        .submit-btn {
            width: 100%;
            background: #2c7edb;
            color: #fff;
            padding: 12px;
            border: none;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
        }

        .submit-btn:hover {
            background: #2568bb;
        }

        .info {
            font-size: 14px;
            color: #555;
            margin-top: 10px;
        }

        #success-message {
            display: none;
            background: #e6ffea;
            border: 1px solid #3ccf72;
            color: #2d8a4f;
            text-align: center;
            font-weight: bold;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        #error-message {
            display: none;
            background: #ffeaea;
            border: 1px solid #ff5c5c;
            color: #d30000;
            text-align: center;
            font-weight: bold;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .back-login {
            text-align: center;
            margin-top: 20px;
        }

        .back-login a {
            color: #2c7edb;
            text-decoration: none;
        }

        .back-login a:hover {
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            .container {
                margin: 20px;
                padding: 20px;
            }
        }
    </style>
</head>

<body>

<!-- Language Switcher -->
<div style="text-align: right; margin: 20px;">
    <label for="language-select">🌐</label>
    <select id="language-select">
        <option value="en">English</option>
        <option value="pt">Português</option>
        <option value="es">Español</option>
    </select>
</div>

<div class="container">
    <div id="success-message">✅ Your account has been successfully created!</div>
    <div id="error-message">❌ Password and Confirm Password must match!</div>

    <h1 data-key="form-title">Create a New Account</h1>
    <form id="signup-form" action="#" method="post">
        <div class="form-group">
            <label data-key="firstname-label" for="firstname">First name *</label>
            <input type="text" id="firstname" name="firstname" required>
        </div>

        <div class="form-group">
            <label data-key="lastname-label" for="lastname">Surname *</label>
            <input type="text" id="lastname" name="lastname" required>
        </div>

        <div class="form-group">
            <label data-key="email-label" for="email">Email address *</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label data-key="username-label" for="username">Username *</label>
            <input type="text" id="username" name="username" required>
        </div>

        <div class="form-group">
            <label data-key="password-label" for="password">Password *</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div class="form-group">
            <label data-key="confirm-label" for="confirmpassword">Confirm Password *</label>
            <input type="password" id="confirmpassword" name="confirmpassword" required>
        </div>

        <div class="form-group">
            <label data-key="city-label" for="city">City/town *</label>
            <input type="text" id="city" name="city" required>
        </div>

        <div class="form-group">
            <label data-key="country-label" for="country">Country *</label>
            <select id="country" name="country" required>
                <option value="" data-key="country-select">Select country</option>
                <option value="PT" data-key="country-pt">Portugal</option>
                <option value="IN" data-key="country-in">India</option>
                <option value="US" data-key="country-us">United States</option>
            </select>
        </div>

        <div class="form-group">
            <label data-key="role-label" for="role">You are a *</label>
            <select id="role" name="role" required>
                <option value="" data-key="role-select">Select one</option>
                <option value="employee" data-key="role-employee">Employee</option>
                <option value="boss" data-key="role-boss">Boss</option>
            </select>
        </div>

        <button type="submit" class="submit-btn" data-key="submit-btn">Create my new account</button>
        <p class="info" data-key="required">* Required fields</p>
    </form>

    <div class="back-login">
        <span data-key="have-account">Already have an account?</span>
        <a href="login.php" data-key="back-login">Back to login</a>
    </div>
</div>

<script>
    const form = document.getElementById('signup-form');
    const successMessage = document.getElementById('success-message');
    const errorMessage = document.getElementById('error-message');

   form.addEventListener('submit', function(e) {
    const password = document.getElementById('password').value.trim();
    const confirm = document.getElementById('confirmpassword').value.trim();

    if (password !== confirm) {
        e.preventDefault(); // Only stop submission if password mismatch
        successMessage.style.display = 'none';
        errorMessage.style.display = 'block';
    }
    });

    <?php if ($accountCreated): ?>
        window.onload = function() {
            successMessage.style.display = 'block';
        };
    <?php endif; ?>

    const translations = {
        en: {
            "form-title": "Create a New Account",
            "firstname-label": "First name *",
            "lastname-label": "Surname *",
            "email-label": "Email address *",
            "username-label": "Username *",
            "password-label": "Password *",
            "confirm-label": "Confirm Password *",
            "city-label": "City/town *",
            "country-label": "Country *",
            "role-label": "You are a *",
            "submit-btn": "Create my new account",
            "required": "* Required fields",
            "have-account": "Already have an account?",
            "back-login": "Back to login",
            "country-select": "Select country",
            "country-pt": "Portugal",
            "country-in": "India",
            "country-us": "United States",
            "role-select": "Select one",
            "role-employee": "Employee",
            "role-boss": "Boss"
        },
        pt: {
            "form-title": "Criar uma Nova Conta",
            "firstname-label": "Nome *",
            "lastname-label": "Apelido *",
            "email-label": "Endereço de email *",
            "username-label": "Nome de usuário *",
            "password-label": "Senha *",
            "confirm-label": "Confirmar Senha *",
            "city-label": "Cidade *",
            "country-label": "País *",
            "role-label": "Você é um(a) *",
            "submit-btn": "Criar minha nova conta",
            "required": "* Campos obrigatórios",
            "have-account": "Já tem uma conta?",
            "back-login": "Voltar ao login",
            "country-select": "Selecionar país",
            "country-pt": "Portugal",
            "country-in": "Índia",
            "country-us": "Estados Unidos",
            "role-select": "Selecione um",
            "role-employee": "Empregado(a)",
            "role-boss": "Chefe"
        },
        es: {
            "form-title": "Crear una Nueva Cuenta",
            "firstname-label": "Nombre *",
            "lastname-label": "Apellido *",
            "email-label": "Correo electrónico *",
            "username-label": "Nombre de usuario *",
            "password-label": "Contraseña *",
            "confirm-label": "Confirmar Contraseña *",
            "city-label": "Ciudad *",
            "country-label": "País *",
            "role-label": "Usted es *",
            "submit-btn": "Crear mi nueva cuenta",
            "required": "* Campos requeridos",
            "have-account": "¿Ya tienes una cuenta?",
            "back-login": "Volver al inicio de sesión",
            "country-select": "Seleccionar país",
            "country-pt": "Portugal",
            "country-in": "India",
            "country-us": "Estados Unidos",
            "role-select": "Seleccione uno",
            "role-employee": "Empleado",
            "role-boss": "Jefe"
        }
    };

    document.getElementById('language-select').addEventListener('change', function() {
        const lang = this.value;
        document.querySelectorAll('[data-key]').forEach(el => {
            const key = el.getAttribute('data-key');
            if (translations[lang][key]) {
                el.textContent = translations[lang][key];
            }
        });
    });
</script>

</body>
</html>