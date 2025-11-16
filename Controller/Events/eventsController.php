<?php
    require_once "../../Model/event.php";
    require_once "../eventsConfig.php";

    if($_SERVER["REQUEST_METHOD"] == "POST")
    {

        if(isset($_POST['deleteID']))
        {
            $id = $_POST['deleteID'];
            Event::delete($pdo, $id);
            header("Location: ../../View/Events/Back Office/events.php");
            exit();
        }


        $title = $_POST["title"];
        $date = new DateTime($_POST["date"]);
        $startTime = new DateTime($_POST["startTime"]);
        $endTime = new DateTime($_POST["endTime"]);
        $course = $_POST["course"];
        $type = $_POST["type"];
        $location = $_POST["location"];
        $recurring = $_POST["recurring"];
        $maxParticipants = $_POST["maxParticipants"];
        $links = $_POST["links"];
        $desc = $_POST["desc"];

        if($type != "Lecture")
        {
            $location = "";
        }

        $teacherID = 0;

        $event = new event( $title, $date,
                            $startTime, $endTime,
                            $maxParticipants, 0,
                            $course, $type,
                            $location, $desc,
                            $teacherID);

        $event->create($pdo);

    }

    header("Location: ../../View/Events/Back Office/events.php");
    exit();
?>
