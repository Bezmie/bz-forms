<?php

////////////////////////////////////////////////////////////////////////////////////////////
// COMMONS /////////////////////////////////////////////////////////////////////////////////

function RandomString($length) {
    $chs = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $str = '';
    for ($i = 0; $i < $length; $i++) {
        $str .= $chs[mt_rand(0, strlen($chs) - 1)];
    }
    return $str;
  }

  function DrawMsg($msg, $type) {
    $prefs = [
        'success' => 'Успех: ',
        'failure' => 'Провал: '
    ];
    $classes = [
        'success' => 'success-bg-color',
        'failure' => 'failure-bg-color'
    ];
    $pref = $prefs[$type] ?? '';
    $class = $classes[$type] ?? '';
    return "<p class='fs-small base-padding $class'>$pref$msg</p>";
}

////////////////////////////////////////////////////////////////////////////////////////////
// FUNC ////////////////////////////////////////////////////////////////////////////////////

function isSqlConnect($arg) {
    return DrawMsg(
        $arg->connect_error 
            ? "Нет содинения с SQL</p>" . $arg->connect_error
            : "Есть соединение с SQL",
        $arg->connect_error 
            ? 'failure' 
            : 'success'
    );
}

function isTableExists($name, $conn) {
    $sql = "SHOW TABLES LIKE '$name'";
    $res = $conn->query($sql);
    return $res->num_rows > 0
        ? DrawMsg("Таблица '$name' существует", "success")
        : DrawMsg("Таблица '$name' не существует", "failure");
}

function createDefaultTable($name, $conn) {
    return $conn->query("CREATE TABLE $name (
        id INT AUTO_INCREMENT PRIMARY KEY,
        string VARCHAR(255) NOT NULL,
        words VARCHAR(255) NOT NULL
    )") 
        ? DrawMsg("Таблица '$name' была создана.", 'success') 
        : DrawMsg("Не удалось создать таблицу '$name'.", 'failure');
}

function displayTable($name, $conn) {
    $res = isTableExists($name, $conn);
    return strpos($res, 'success') !== false
        ? "<h3 class='base-padding'>TABLE: $name</h3>"
           . tableDeleteForm($name)
           . "<table class='fs-small'>
           <thead class='txt-align-left'>"
           . implode('', array_map(function($field) {
               return "<th class='base-padding txt-uppercase'>" . $field->name . "</th>";
           }, $conn->query("SELECT * FROM `$name`")->fetch_fields()))
           . "<th class='base-padding'>ACTION</th>
           </thead>"
           . implode('', array_map(function($row) use ($name, $conn) {
               return "<tr class='base-padding'>"
                   . implode('', array_map(function($value) {
                       return "<td class='base-padding'>$value</td>";
                   }, $row))
                   . "<td class='base-padding'>"
                   . rowDeleteForm($row["id"])
                   . "</td>
                   </tr>";
           }, $conn->query("SELECT * FROM `$name`")->fetch_all(MYSQLI_ASSOC)))
           . rowAddForm($name)
           . "</table>"
        : createTableForm($name);
}

function createTableForm($name) {
    return "<form class='m-0' method='post' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "'>
            <input type='hidden' name='action' value='create_table'>
            <input type='hidden' name='table_name' value='$name'>
            <button type='submit' class='create-btn fs-small'>CREATE DEFAULT TABLE</button>
            </form>";
}

function tableDeleteForm($name) {
    return "<form class='m-0' method='post' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "'>
            <input type='hidden' name='action' value='delete_table'>
            <input type='hidden' name='table_name' value='$name'>
            <button type='submit' class='delete-btn fs-small'>DROP TABLE</button>
            </form>";
}

function deleteTableData($name, $conn) {
    $sql = "DROP TABLE `$name`";
    return $conn->query($sql) === TRUE
            ? DrawMsg("Таблица '$name' была успешно удалена.", "success")
            : DrawMsg("Ошибка при удалении таблицы '$name': " . $conn->error, 'failure');
}

function rowDeleteForm($id) {
    return "<form class='m-0' method='post' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "'>
            <input type='hidden' name='action' value='delete_row'>
            <input type='hidden' name='delete_id' value='$id'>
            <button type='submit' class='delete-btn fs-small'>DELETE</button>
            </form>";
}

function rowDeleteData($name, $conn) {
    if (isset($_POST['delete_id'])) {
        $id = $conn->real_escape_string($_POST['delete_id']);
        $sql = "DELETE FROM `$name` WHERE id = '$id'";
        return $conn->query($sql) === TRUE
            ? DrawMsg("Строка с ID $id удалена.", "success")
            : DrawMsg("Ошибка при удалении строки: " . $conn->error, 'failure');
    }
    return "";
}

function rowAddForm($name) {
    return "<tr class='base-padding'>
            <td class='base-padding'></td>
            <td class='base-padding'></td>
            <form class='m-0' method='post' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "'>
            <input type='hidden' name='action' value='add_row'>
            <input type='hidden' name='table_name' value='$name'>
            <td class='base-padding'><input type='text' name='words'required placeholder='...' class='fs-small'></td>
            <td class='base-padding'><button type='submit' class='add-btn fs-small'>ADD</button></td>
            </form>
            </tr>";
}

function rowAddData($name, $conn) {
    if (isset($_POST['words'])) {
        $string = RandomString(16);
        $words = $conn->real_escape_string($_POST['words']);
        $sql = "INSERT INTO `$name` (id, string, words) VALUES (NULL, '$string','$words')";
        return $conn->query($sql) === TRUE
            ? DrawMsg("Новая строка с ID {$conn->insert_id} добавлена.", "success")
            : DrawMsg("Ошибка при добавлении строки: " . $conn->error, 'failure');
    }
    return "";
}

////////////////////////////////////////////////////////////////////////////////////////////
// BACK ////////////////////////////////////////////////////////////////////////////////////

$conn = new mysqli("mysql", "user", "word", "base");

$table_prime = "MAIN";

/////////////////////////////////////////////////////////////////////////////////////////////
// CLIENT ///////////////////////////////////////////////////////////////////////////////////

echo isSqlConnect($conn);

echo isTableExists($table_prime, $conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    switch ($action) {
        case 'create_table': 
            if (strpos(isTableExists($table_prime, $conn), 'failure') !== false) {
                echo createDefaultTable($table_prime, $conn);
            } 
            break;
        case 'delete_table':
            if (strpos(isTableExists($table_prime, $conn), 'success') !== false) {
                echo deleteTableData($table_prime, $conn);
            } break;
        case 'delete_row': echo rowDeleteData($table_prime, $conn); break;
        case 'add_row': echo rowAddData($table_prime, $conn); break;
        default: break;
    }
}

echo displayTable($table_prime, $conn);

$conn->close();

?>
<style>
:root {
  --base-padding: 8px 10px;
  --base-bg-color: rgb(240 240 240);
  --success-bg-color: rgb(80 180 80);
  --failure-bg-color: rgb(230 140 130);
  --fs-normal: 1rem;
  --fs-small: 0.8rem;
}
body {
    display: flex;
    flex-direction: column;
    font-family: sans-serif;
    gap: 10px;
}
section {
    width: 100%;
    max-width: 940px;
    margin: auto;
    padding: var(--base-padding);
    border-radius: 10px;
    background-color: var(--base-bg-color);
}
table {width: 100%; border-collapse: collapse;}
tbody > tr:nth-of-type(even) {background-color: var(--base-bg-color);}
tbody > tr:nth-of-type(odd) {background-color: white;}
.success-bg-color {background-color:var(--success-bg-color); border-left: 4px solid;}
.failure-bg-color {background-color:var(--failure-bg-color); border-left: 4px solid;}
.base-padding {padding: var(--base-padding);}
.fs-small{font-size:var(--fs-small)}
.txt-uppercase {text-transform: uppercase;}
.txt-align-left {text-align: left;}
.txt-align-right {text-align: right;}
.form-group {margin-bottom: 20px;}
.m-0 {margin:0;}
input[type="text"],
input[type="email"],
textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 3px;
    font-size: 16px;
}
textarea {
    height: 100px;
}
button[type="submit"] {
    width: 100%;
    background-color: #4CAF50;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 3px;
    cursor: pointer;
}
button[type="submit"]:hover {
    background-color: #45a049;
}
</style>