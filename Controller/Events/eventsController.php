<?php

    function createEvent($pdo, $event)
    {
        $statement = $pdo->prepare("
            INSERT INTO events 
            (title, date, startTime, endTime, maxParticipants, nbrParticipants, course, type, location, description, teacherID) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $statement->execute([
            $event->title,
            $event->date->format('Y-m-d'),
            $event->startTime->format('H:i:s'),
            $event->endTime->format('H:i:s'),
            $event->maxParticipants,
            $event->nbrParticipants,
            $event->course,
            $event->type,
            $event->location,
            $event->description,
            $event->teacherID
        ]);
    }

    function deleteEvent($pdo, int $id)
    {
        $statement = $pdo->prepare("DELETE FROM events WHERE eventID = ?");
        $statement->execute([$id]);
    }

    function incrementParticipantEvent($pdo, $id)
    {
        $statement = $pdo->prepare("UPDATE events SET nbrParticipants = nbrParticipants + ? WHERE eventID = ?");
        $statement->execute([1, $id]);
    }

    function decrementParticipantEvent($pdo, $id)
    {
        $statement = $pdo->prepare("UPDATE events SET nbrParticipants = nbrParticipants - ? WHERE eventID = ?");
        $statement->execute([1, $id]);
    }

    function getAllEvents($pdo)
    {
        $statement = $pdo->prepare("SELECT * FROM events");
        $statement->execute();
        $events = $statement->fetchAll(PDO::FETCH_ASSOC);
    
        return $events;
    }

    function getAllTeacherEvents($pdo, $teacherID)
    {
        $statement = $pdo->prepare("SELECT * FROM events WHERE teacherID = ?");
        $statement->execute([$teacherID]);
        $events = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $events;
    }

    function createParticipation($pdo, Participation $participation)
    {
        $statement = $pdo-> prepare("
            INSERT INTO participation
            (id_event, id_user, attachement, comment)
            VALUES (?, ?, ?, ?)  ");

        $statement->execute([
            $participation->getEventID(),
            $participation->getUserID() ,
            "Hello",
            "pppp"
        ]);
    }

    function isUserInEvent($pdo, $userID, $eventID)
    {
        $statement = $pdo->prepare("SELECT * FROM participation WHERE id_event = ? AND id_user = ?");
        $statement->execute([$eventID, $userID]);

        return $statement->fetch() !== false;
    }

    function deleteParticipation($pdo, $userID, $eventID)
    {
        $statement = $pdo->prepare("DELETE FROM participation WHERE id_event = ? AND id_user = ?");
        $statement->execute([$eventID, $userID]);
    }
    
    function deleteAllEventParticipations($pdo, $eventID)
    {
        $statement = $pdo->prepare("DELETE FROM participation WHERE id_event = ?");
        $statement->execute([$eventID]);
    }
?>



<?php
    require_once __DIR__ . "/../../Model/event.php";
    require_once __DIR__ . "/eventsConfig.php";

    if($_SERVER["REQUEST_METHOD"] == "POST")
    {

        if(isset($_POST['deleteID']))
        {
            $id = $_POST['deleteID'];
            deleteEvent($pdo, $id);
            deleteAllEventParticipations($pdo, $id);

            if(isset($_POST['admin']))
                header("Location: ../../View/Events/Back Office/eventsAdmin.php");
            else
                header("Location: ../../View/Events/Front Office/eventsTeacher.php");
            exit();
        }
        else if(isset($_POST['studentID']))
        {
            $eventID = $_POST['eventID'];
            $userID = $_POST['studentID'];
            if($_POST['leave'])
            {
                deleteParticipation($pdo, $userID, $eventID);
                decrementParticipantEvent($pdo, $eventID);
            }
            else
            {
                $participation = new Participation($eventID, $userID);
                createParticipation($pdo, $participation);
                incrementParticipantEvent($pdo, $eventID);
            }

            header("Location: ../../View/Events/Front Office/eventsFront.php");
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

        $teacherID = 1;

        $event = new event( $title, $date,
                            $startTime, $endTime,
                            $maxParticipants, 0,
                            $course, $type,
                            $location, $desc,
                            $teacherID);

        createEvent($pdo, $event);
        header("Location: ../../View/Events/Front Office/eventsTeacher.php");
        exit();
    }


?>
