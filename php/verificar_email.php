<?php
// 1. Include the database connection
require_once 'conexion.php'; 

// 2. Check if required data was sent via POST
if (!isset($_POST['email'], $_POST['user_type'])) {
    // Redirect if accessed directly or missing data
    header("Location: ../html/Recuperar_Contrasenia.php?error=missing_data");
    exit();
}

// 3. Get the submitted data
$email = $_POST['email'];
$user_type = $_POST['user_type'];

// 4. Determine the correct table and ID column based on user type
$table = ($user_type === 'admin') ? 'Empleado' : 'Cliente';
$id_column = ($user_type === 'admin') ? 'id_empleado' : 'id_cliente';
// Add the role check only if verifying an admin
$role_check_sql = ($user_type === 'admin') ? " AND rol = 'admin'" : "";

// 5. Prepare the SQL query to find the user
$sql = "SELECT $id_column FROM $table WHERE email = ? $role_check_sql";
$stmt = mysqli_prepare($conexion, $sql);

// Check if the query preparation failed
if (!$stmt) {
    die("Error preparing query: " . mysqli_error($conexion));
}

// 6. Bind the email parameter and execute
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

// 7. Check if a user was found
if (mysqli_num_rows($resultado) > 0) {
    // Email exists: Redirect to the page for entering the new password.
    // Pass both email and user_type in the URL.
    header("Location: nueva_contrasenia.php?email=" . urlencode($email) . "&type=" . $user_type);
    exit();
} else {
    // Email not found (or not an admin): Redirect back to the recovery form with an error.
    header("Location: ../html/Recuperar_Contrasenia.php?error=no_encontrado&type=" . $user_type);
    exit();
}

// Close statement and connection (though exit() usually stops execution before this)
mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>