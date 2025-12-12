

//alert("hello from dev team");

function checkInputs(events)
{
    const title = document.getElementById("title").value.trim();
    const date = document.getElementById("date").value;
    const start = document.getElementById("startTime").value;
    const end = document.getElementById("endTime").value;
    const maxP = document.getElementById("maxParticipants").value;
    const course = document.getElementById("course").value;
    const type = document.getElementById("type").value;
    const location = document.getElementById("location").value;
    const description = document.getElementById("desc").value;

    if (!title) 
    {
        events.preventDefault();
        let err = document.getElementById("errTitle");
        err.textContent = "Title is required";
        err.style.color = "red";
    }

    if (!date) 
    {
        events.preventDefault();
        let err = document.getElementById("errDate");
        err.textContent = "Date is required";
        err.style.color = "red";
    }

    if (!start) 
    {
        events.preventDefault();
        let err = document.getElementById("errStartTime");
        err.textContent = "Start Time is required";
        err.style.color = "red";
    }

    if (!end) 
    {
        events.preventDefault();
        let err = document.getElementById("errEndTime");
        err.textContent = "End Time is required";
        err.style.color = "red";
    }

    if (start && end && end <= start) 
    {
        events.preventDefault();
        let err = document.getElementById("errTimeDifference");
        err.textContent = "End Time can't be before Start Time";
        err.style.color = "red";
    }

    if(!course)
    {
        events.preventDefault();
        let err = document.getElementById("errCourse");
        err.textContent = "Course Name is required";
        err.style.color = "red";
    }

    if(type == "Lecture" && !location)
    {
        events.preventDefault();
        let err = document.getElementById("errLocation");
        err.textContent = "Location is required for Lecture Type";
        err.style.color = "red";
        
    }


    if (!maxP || maxP < 1)
    {
        events.preventDefault();
        let err = document.getElementById("errParticipants");
        err.textContent = "Number of Particiapants must be at least 1";
        err.style.color = "red";
    }

    if(!description)
    {
        events.preventDefault();
        let err = document.getElementById("errDesc");
        err.textContent = "Description is required";
        err.style.color = "red";
    }
}

function addLocation()
{
    let type = document.getElementById("type");

    let err = document.getElementById("errLocation");
    let location = document.getElementById("location");
    let locationLabel = document.getElementById("locationLabel");
    
    if(type.value == "Lecture")
    {
        location.style.display = "block";
        locationLabel.style.display = "block";
        err.style.display = "block";
    }
    else
    {
        location.style.display = "none";
        locationLabel.style.display = "none";
        err.style.display = "none";
        
    }
}

addEventListener("DOMContentLoaded", addLocation);

const form = document.getElementById("addEventForm");
form.addEventListener("submit", checkInputs);
const type = document.getElementById("type");
type.addEventListener("input", addLocation);



