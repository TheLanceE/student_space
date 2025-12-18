<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Add Task (plain)</title>
  <link href="../../assets/vendor/bootstrap.min.css" rel="stylesheet">
  <style>body{padding:28px;font-family:Arial,Helvetica,sans-serif}</style>
</head>
<body>
  <h3>New Task (plain test)</h3>
  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Task Name</label>
      <input class="form-control" name="data[taskName]" minlength="4">
    </div>
    <div class="mb-3">
      <label class="form-label">Project</label>
      <select class="form-select" name="data[projectId]">
        <option value="proj_6d424fad55179239" selected>life</option>
        <option value="proj_80e1913ba234beca">health</option>
        <option value="proj_a2334a2aa165c0a7">health</option>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">Description</label>
      <textarea class="form-control" name="data[description]"></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">Due Date</label>
      <input type="date" class="form-control" name="data[dueDate]">
    </div>
    <button class="btn btn-primary" type="submit">Create</button>
    <a class="btn btn-secondary" href="taskList.php">Cancel</a>
  </form>
  <p style="margin-top:20px;color:#666">This page intentionally has no validation JS — use it to confirm whether inputs accept typing.</p>
</body>
</html>