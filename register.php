<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration | SkillLink UNIMTECH</title>
</head>
<body>

<h1>Student Registration</h1>

<form action="/SkillLink-UNIMTECH/register_process.php" method="POST">

    <label>Full Name</label><br>
    <input type="text" name="full_name" required><br><br>

    <label>Student ID</label><br>
    <input type="text" name="student_id" required><br><br>

    <label>Email</label><br>
    <input type="email" name="email" required><br><br>

    <label>Password</label><br>
    <input type="password" name="password" required><br><br>

    <label>Department</label><br>
    <input type="text" name="department"><br><br>

    <label>Programme</label><br>
    <input type="text" name="programme"><br><br>

    <label>Level</label><br>
    <select name="level">
        <option value="">Select Level</option>
        <option>100</option>
        <option>200</option>
        <option>300</option>
        <option>400</option>
    </select><br><br>

    <button type="submit">Register</button>

</form>

</body>
</html>