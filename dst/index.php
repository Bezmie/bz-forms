<?php ?>
<section>
<?php

function test () {echo ("
    <h3 class='base-padding'>INFO</h3>
    <p class='fs-small base-padding success-bg-color'>TEST</p>
");};

test();

////

$conn = new mysqli("mysql", "user", "word", "base");

if ($conn->connect_error) {
  die("<p class='fs-small base-padding failure-bg-color'>Нет содинения с SQL</p>" . $conn->connect_error);
} else {
  echo "<p class='fs-small base-padding success-bg-color'>Есть соединение с SQL</p>";
}

$t_name = "DATATABLE";

$sql = "SHOW TABLES LIKE '$t_name'";
$res = $conn->query($sql);

if ($res->num_rows == 0) {
    $sql = "CREATE TABLE $t_name (
      id INT AUTO_INCREMENT PRIMARY KEY,
      string VARCHAR(255) NOT NULL,
      words VARCHAR(255) NOT NULL
    )";
    if ($conn->query($sql) === TRUE) {
        echo "<p class='fs-small base-padding success-bg-color'>Создана таблица с именем: $t_name</p>";
    } else {
        echo "<p class='fs-small base-padding failure-bg-color'>DATA NOT CREATED</p>" . $conn->error;
    }
} else {
    echo "<p class='fs-small base-padding success-bg-color'>Существует таблица с именем: $t_name</p>";
}

?>
</section>
<section>
<?php

    function form () {echo ('
        <h3 class="base-padding">FORM</h3>
        <form method="post" action="'.htmlspecialchars($_SERVER["PHP_SELF"]).'">
            <div class="form-group"> 
                <label for="words">WORDS</label>
                <input type="text" id="words" name="words"></input>
            </div>
            <input type="hidden" name="string" value="' . genRandStr(16) . '">
            <button type="submit">SUBMIT</button>
        </form>
    ');};

    form();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' ) {
        if (!empty($_POST['words'])) {
            $words = $_POST['words'];
            $string = $_POST['string'];
            $sql = "INSERT INTO $t_name (id, string, words) VALUES (NULL, '$string', '$words')";
            if ($conn->query($sql) === TRUE) {
                echo "<p class='fs-small base-padding success-bg-color'>ADDED: {$words} </p>";
            } else {
                echo "<p class='fs-small base-padding failure-bg-color'>NOT ADDED . $conn->error</p>";
            }
        } else {
            echo "<p class='fs-small base-padding failure-bg-color'>Поле WORDS не было заполнено в форме.</p>";
        }
    }

?>
</section>
<section>
<?php

$sql = "SELECT * FROM `{$t_name}`";
$res = $conn->query($sql);

if ($res->num_rows > 0) {
    $table_name = $res->fetch_fields()[0]->table;
    echo "<h3 class='base-padding'>TABLE: $table_name</h3>";
    echo "<table class='fs-small'>";
    echo "<thead class='txt-align-left'><th class='base-padding'>ID</th><th class='base-padding'>STRING</th><th class='base-padding'>WORDS</th><th class='base-padding'>ACTION</th></thead>";
    while ($row = $res->fetch_assoc()) {
        echo "<tr class='base-padding'>";
        echo "<td class='base-padding'>" . $row["id"] . "</td>";
        echo "<td class='base-padding'>" . $row["string"] . "</td>";
        echo "<td class='base-padding'>" . $row["words"] . "</td>";
        echo "<td class='base-padding'>";
        echo "<form class='m-0' method='post' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "'>";
        echo "<input type='hidden' name='delete_id' value='" . $row["id"] . "'>";
        echo "<button type='submit' class='delete-btn fs-small'>DELETE</button>";
        echo "</form>";
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_id"])) {
        $delete_id = $_POST["delete_id"];
        $delete_sql = "DELETE FROM `{$t_name}` WHERE id = $delete_id";
        if ($conn->query($delete_sql) === TRUE) {
            echo "<p class='fs-small base-padding success-bg-color'>Строка с ID $delete_id удалена</p>";
        } else {
            echo "<p class='fs-small base-padding failure-bg-color'>Ошибка при удалении: " . $conn->error . "</p>";
        }
    }
} else {
    echo "<p class='fs-small base-padding failure-bg-color'>DATA EMPTY</p>";
}

$conn->close();

?>
    </section>
<?php

function genRandStr($length) {
  $chs = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $str = '';
  for ($i = 0; $i < $length; $i++) {
      $str .= $chs[mt_rand(0, strlen($chs) - 1)];
  }
  return $str;
}

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