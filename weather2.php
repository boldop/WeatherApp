<html>
<head>
</head>
<body>

<form method="get" action="">
  <input type="text" name="city" placeholder="Enter city name">
  <input type="submit" name="submit" value="Search">
</form>

<style>
body {
  background-color: #ff004f;
  font-family: 'Roboto', sans-serif;
}

h1 {
  text-align: center;
  margin-top: 50px;
  color: #555;
}

form {
  display: flex;
  justify-content: center;
  margin-top: 20px;
}

input[type="text"] {
  padding: 10px;
  font-size: 16px;
  border-radius: 5px 0 0 5px;
  border: none;
  width: 300px;
  transition: all 0.3s ease-in-out;
  box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
}

input[type="text"]:focus {
  outline: none;
  box-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
}

input[type="submit"] {
  padding: 10px;
  font-size: 16px;
  border-radius: 0 5px 5px 0;
  border: none;
  width: 100px;
  background-color: #ff004f;
  color: #fff;
  transition: all 0.3s ease-in-out;
  cursor: pointer;
  box-shadow: 0 0 5px rgb(203, 129, 203); 
}

input[type="submit"]:hover {
  background-color:  red;
}

table {
  border-collapse: collapse;
  margin: 20px auto;
  font-size: 16px;
  box-shadow: 0 0 10px rgb(203, 129, 203);
}

table th,
table td {
  padding: 10px;
  text-align: center;
}

table th {
  background-color: #ff004f;
  color: #fff;
}

table tr:nth-child(odd) {
  background-color: #ff004f;
}

table tr:hover {
  background-color: lightgreen;
}
body {
  background-color: lightblue;
}


</style>
  
<?php

if (isset($_GET['submit'])) {
  $city = $_GET['city'];
} else {
  $city = "philadelphia";
}

$url = "https://api.openweathermap.org/data/2.5/weather?q={$city}&appid=86a764941608701091c171ee380516c9&units=metric";

// Make API request and parse JSON response
$response = file_get_contents($url);
$data = json_decode($response, true);

if (!$data) {
  // Handle API error
  die("Error: Failed to retrieve data from OpenWeatherMap API.");
}

// Extract relevant weather data
$city_name = $data['name'];
$condition = $data['weather'][0]['main'];
$icon = $data['weather'][0]['icon'];
$temperature = $data['main']['temp'];
$pressure = $data['main']['pressure'];
$humidity = $data['main']['humidity'];
$wind_speed = $data['wind']['speed'];
$rainfall = isset($data['rain']['1h']) ? $data['rain']['1h'] : 'not given';


// Insert or update weather data in database
$host = 'localhost';
$username = 'root';
$password = "";
$dbname = 'weather2';

$conn = mysqli_connect("localhost","root", "", "weather2");

if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}else{
  echo"Connection established";
}

// Check if data for the current hour is already present in database
$sql = "SELECT * FROM datw WHERE `city`='$city_name' AND DATE(`date`) = CURDATE()";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
  // Update existing row with latest weather data
  $sql = "UPDATE datw SET `condition`='$condition', `icon`='$icon', `temperature`='$temperature', `rainfall`=0, `humidity`='$humidity', `wind_speed`='$wind_speed' WHERE `city`='$city_name' AND `date`= DATE_FORMAT(NOW(), '%Y-%m-%d %H:00:00')";
} else {
  // Insert new row with current weather data
 
  $sql = "INSERT INTO datw (`city`, `date`, `condition`, `icon`, `temperature`, `rainfall`, `wind_speed`, `humidity`)
        VALUES ('$city_name', NOW(), '$condition', '$icon', '$temperature', '0', '$wind_speed', '$humidity')";
}

mysqli_query($conn, $sql);

// Retrieve latest weather data from database
$sql = "SELECT * FROM  datw WHERE `city`='$city_name' ORDER BY `date` DESC LIMIT 7";
$result = mysqli_query($conn, $sql);

echo "<table border='1'>";
echo "<tr>";
echo "<th>Date/Time</th>";
echo "<th>Condition</th>";
echo "<th>Icon</th>";
echo "<th>Temperature</th>";
echo "<th>Humidity</th>";
echo "<th>Wind Speed</th>";
echo "</tr>";
while ($row = mysqli_fetch_assoc($result)) {
  $date = date('Y-m-d H:i:s', strtotime($row['date']));
  $condition = $row['condition'];
  $icon = $row['icon'];
  $temperature = $row['temperature'];
  $humidity = $row['humidity'];
  $wind_speed = $row['wind_speed'];

  echo "<tr>";
  echo "<td>{$date}</td>";
  echo "<td>{$condition}</td>";
  echo "<td><img src='http://openweathermap.org/img/w/{$icon}.png'></td>";
  echo "<td>{$temperature}°C</td>";
  echo "<td>{$humidity}%</td>";
  echo "<td>{$wind_speed} m/s</td>";
  echo "</tr>";
}
echo "</table>";


// Close database connection
mysqli_close($conn);
?>
</body>
</html>