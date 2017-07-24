<!--
Francisco Camacho (Dec., 2016, Madrid)

version 1.0:  no object-oriented, only inserts in Database 
version 1.5:  object-oriented, only inserts in Database    
version 2.0:  object-oriented, inserts in Database, and read xml file from URL 
version 2.1:  added xml validation
-->

<!DOCTYPE HTML>  
<html>
<head>
<style>
.error {color: #FF0000;}
</style>
</head>
<body>  


<?php

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
//class Contact:
//
// it does everything, except the forms.
// I tried it too, but it was a bit complicated, 
// so I decided to split the logic, from the way the info is obtained -> 
//   html : forms, 
//   php code: all the logic
//
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
class Contact {

  private $firstName; 
  private $lastName; 
  private $address;  
  private $email; 
  private $phone;
  // attr. to save error messages for the user:
  private $firstNameErr; 
  private $lastNameErr; 
  private $emailErr; 
  private $phoneErr;
  //to output the result of the insertion in the database:
  private $insertResultDescr;  
  //version 2.0
  private $xmlFile;

    
  //Constructor version 2.0
  public function __construct() {
  }

  //Static constructor/factory
  public static function create() {
    $instance = new self();
    return $instance;
  }

  // firstName setter - fluent style
  public function setFirstName($firstName) {
    $this->firstName = $firstName;
    return $this;
  }

  // lastName setter - fluent style
  public function setLastName($lastName) {
    $this->lastName = $lastName;
    return $this;
  }

  // address setter - fluent style
  public function setAddress($address) {
    $this->address = $address;
    return $this;
  }

  // email setter - fluent style
  public function setEmail($email) {
    $this->email = $email;
    return $this;
  }

  // phone setter - fluent style
  public function setPhone($phone) {
    $this->phone = $phone;
    return $this;
  }

  // xmlFile setter
  public function setInsertResultDescr($str) {
    $this->insertResultDescr = $str;
  }

  // xmlFile setter - fluent style
  public function setXmlFile($xmlFile) {
    $this->xmlFile = $xmlFile;
    return $this; 
  }


  //getters
  function getFirstName() {
    return $this->firstName;
  }

  function getLastName() {
    return $this->lastName;
  }

  function getAddress() {
    return $this->address;
  }

  function getEmail() {
    return $this->email;
  }

  function getPhone() {
    return $this->phone;
  }

  function getFirstNameErr() {
    return $this->firstNameErr;
  }

  function getLastNameErr() {
    return $this->lastNameErr;
  }

  function getEmailErr() {
    return $this->emailErr;
  }

  function getPhoneErr() {
    return $this->phoneErr;
  }

  function getInsertResultDescr() {
    return $this->insertResultDescr;
  }

  function getXmlFile() {
    return $this->xmlFile;
  }


  /*__construct in version 1.5
  function __construct( $par1, $par2, $par3, $par4, $par5 ) {
    $this->firstName = $par1;
    $this->lastName = $par2;
    $this->address = $par3;
    $this->email = $par4;
    $this->phone = $par5;
  }
  */


  //toString
  function toString() {
    return "(".$this->firstName.",".$this->lastName.",".$this->address.",".$this->email.",".$this->phone.")";
  }


  //To avoid $_SERVER["PHP_SELF"] exploits, we treat the input of the form
  private function treat_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
  }


  //check the info received from the form\xml file
  function checkFields() {

     $fieldsOk = 1;

    //First Name
    if (empty($this->firstName) || strlen($this->firstName)==0) {
      $this->firstNameErr = "First Name is required";
      $fieldsOk = 0;
    } 
    else {
       $this->firstName = $this->treat_input($this->firstName);
      //only letters and whitespaces allowed
      if (!preg_match("/^[a-zA-Z ]*$/",$this->firstName)) {
        $this->firstNameErr = "Only letters and white spaces are allowed in First Name";
        $fieldsOk = 0;
      }
    }

    //Last Name
    if (empty($this->lastName) || strlen($this->lastName)==0) {
      $this->lastNameErr = "Last Name is required";
      $fieldsOk = 0;
    } 
    else {
      $this->lastName = $this->treat_input($this->lastName);
      //only letters and whitespaces allowed
      if (!preg_match("/^[a-zA-Z ]*$/",$this->lastName)) {
        $this->lastNameErr = "Only letters and white spaces are allowed in Last Name";
        $fieldsOk = 0;
      }
    }

    //Address is optional
    if (!empty($this->address)) {
      $this->address = $this->treat_input($this->address);
    } 

    //Email 
    if (empty($this->email) || strlen($this->email)==0) {
      $this->emailErr = "Email is required";
      $fieldsOk = 0;
    } else {
      $this->email = $this->treat_input($this->email);
      // check if e-mail address is well-formed
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
          $this->emailErr = "Invalid email format";
          $fieldsOk = 0;
        }
    }
    
    //Phone number is optional (but if given, only numbers, white spaces and "-" are allowed -no special format-)
    if (!empty($this->phone)) {
      $this->phone = $this->treat_input($this->phone);
      if (!preg_match("/^[0-9 -]*$/",$this->phone)) {
        $this->phoneErr = "Only numbers, '-', and white spaces are allowed in phone number";
        $fieldsOk = 0;
      }
    } 

    return  $fieldsOk;
  }


  //to insert info in the database
  function insertInDatabase() { 

    $servername = "localhost";
    $username = "fran";
    $password = "fran";
    $dbname = "exercise";

    try {
      $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
      // set the PDO error mode to exception
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   
      $sql = "INSERT INTO contact (firstname, lastname, address, email, phone)
      VALUES ('$this->firstName', '$this->lastName', '$this->address', '$this->email', '$this->phone')";
      // use exec() because no results are returned
      $conn->exec($sql);
      $this->insertResultDescr = "New record created successfully for ($this->firstName,$this->lastName)";

    //$conn = null;
    }
    catch(PDOException $e) {
      $this->insertResultDescr = $sql."<br>".$e->getMessage();
    }
  }

  //version 2.1
  //validate the xml file according with schemaContact.xsd
  function validateXML($xmlText) {

    $xmlDom = new DOMDocument();
    $xmlDom->load($xmlText);

    if (!$xmlDom->schemaValidate('schemaContact.xsd')) {
      //libxml_display_errors();
      return 0;
    }
    else {
      return 1;
    }

  }

}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END of class Contact
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////


// form variables
$formFirstName = $formLastName = $formAddress = $formEmail = $formPhone = "";
$formFirstNameErr = $formLastNameErr = $formEmailErr = $formPhoneErr = "";
$formXmlFile = "";


//POST for the form
if ($_SERVER["REQUEST_METHOD"] == "POST") {

  //initialize Contact object
  $contact = Contact::create();

  //pass variables from form to object (using its constructor)
  // version 1.5
  //$contact = new Contact($_POST["formFirstName"],$_POST["formLastName"],$_POST["formAddress"],$_POST["formEmail"],$_POST["formPhone"]);    
  // version 2.0
  $contact = $contact->setFirstName($_POST["formFirstName"])->setLastName($_POST["formLastName"])->setAddress($_POST["formAddress"])->setEmail($_POST["formEmail"])->setPhone($_POST["formPhone"]);

  //validate info
  $fieldsOk = $contact->checkFields();

  if ($fieldsOk) {
    //if ok, call the object's function to insert info into the database
    $contact->insertInDatabase();
    //and clean form:
    $formFirstName = $formLastName = $formAddress = $formEmail = $formPhone = "";
    $formFirstNameErr = $formLastNameErr = $formEmailErr = $formPhoneErr = "";
  }
  else {
    //pass variables from object to form to inform the user
    $formFirstName = $contact->getFirstName();
    $formLastName = $contact->getLastName();
    $formAddress = $contact->getAddress();
    $formEmail = $contact->getEmail();
    $formPhone = $contact->getPhone();

    $formFirstNameErr = $contact->getFirstNameErr();
    $formLastNameErr = $contact->getLastNameErr();
    $formEmailErr = $contact->getEmailErr();
    $formPhoneErr = $contact->getPhoneErr();
  }

} //POST


//GET for the xml file in URL
if ($_SERVER["REQUEST_METHOD"] == "GET") {

  //initialize Contact object
  $contact = Contact::create();

  if (isset($_GET['xml'])) {
    $contact = $contact->setXmlFile($_GET['xml']);
    //pass variables from object to form
    $formXmlFile = $contact->getXmlFile();
    
    //parse&validate xml file
    if (!empty($formXmlFile)) {

      if (file_exists($formXmlFile)) {
     
	$validationOk=$contact->validateXML($formXmlFile);
        if ($validationOk) {
          $xmlText=simplexml_load_file($formXmlFile) or die("Error: Cannot read xml file!");

          // version 2.0
          $contact = $contact->setFirstName($xmlText->firstname)->setLastName($xmlText->lastname)->setAddress($xmlText->address)->setEmail($xmlText->email)->setPhone($xmlText->phone);
          
          $xmlOk = $contact->checkFields();
          if ($xmlOk) {
            //call the object's function to insert info into the database
            $contact->insertInDatabase();
          }
          else {
            //tell the user
            $contact->setInsertResultDescr("Info in xml file not ok. Nothing done."); //we use the same variable, no need to add another
          }

        } //validationOk
        else {
          $contact->setInsertResultDescr("Xml file not valid. Nothing done.");
        }
      }  
      else {
         $contact->setInsertResultDescr("Given file does not exist!");
      }

    }//empty
    else {
       $contact->setInsertResultDescr("No file was given!");
    }
  } //isset

} //GET

?>



<br>
<h2>Brain-TEC exercise</h2>
<h3>Author: Fran Camacho (Madrid, Dec. 2016)</h3>
<br>
<br>

<form method = "get" action="<?php $_PHP_SELF ?>" >
    xmlFile: <input type="text" name="xml" value="<?php echo $formXmlFile;?>" />
    <input type="submit"/>
</form>

<br>

<p>
<span class="error">* required field.</span>
</p>
<br>

<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">  

  First Name: <input type="text" name="formFirstName" value="<?php echo $formFirstName;?>">
  <span class="error">* <?php echo $formFirstNameErr;?></span>
  <br>
  <br>
  
  Surname: <input type="text" name="formLastName" value="<?php echo $formLastName;?>">
  <span class="error">* <?php echo $formLastNameErr;?></span>
  <br>
  <br>

  Address: <input type="text" name="formAddress" value="<?php echo $formAddress;?>">
  <br>
  <br>

  E-mail: <input type="text" name="formEmail" value="<?php echo $formEmail;?>">
  <span class="error">* <?php echo $formEmailErr;?></span>
  <br>
  <br>

  Telephone Number: <input type="text" name="formPhone" value="<?php echo $formPhone;?>">
  <span class="error"><?php echo $formPhoneErr;?></span>
  <br>
  <br> 

  <br><br>
  <input type="submit" name="submit" value="Submit">  
</form>

<?php
echo "<br>";
echo "<br>";
echo $contact->getInsertResultDescr();
?>

</body>
</html>
