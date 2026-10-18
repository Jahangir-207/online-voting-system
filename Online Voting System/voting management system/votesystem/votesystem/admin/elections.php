<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>
  <div class="content-wrapper" style="background-color:#F1E9D2">
    <section class="content-header">
      <h1><b>Election Schedule</b></h1>
    </section>
    <section class="content">
      <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?></div>
      <?php endif; ?>
      <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success']); ?></div>
      <?php endif; ?>
      <div class="box" style="background-color:#d8d1bd">
        <div class="box-header with-border">
          <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addElection"><i class="fa fa-plus"></i> New Election</button>
        </div>
        <div class="box-body">
          <table class="table">
            <thead><tr><th>Name</th><th>Type</th><th>Area</th><th>Start</th><th>End</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php
              $query = $conn->query("SELECT e.*, et.name AS election_type, s.name AS state_name, d.name AS district_name, c.name AS constituency_name
                FROM elections e
                LEFT JOIN election_types et ON et.id = e.election_type_id
                LEFT JOIN states s ON s.id = e.state_id
                LEFT JOIN districts d ON d.id = e.district_id
                LEFT JOIN constituencies c ON c.id = e.constituency_id
                ORDER BY e.start_at DESC, e.id DESC");
              while($row = $query->fetch_assoc()):
                $now = time();
                $start = strtotime($row['start_at']);
                $end = strtotime($row['end_at']);
                $displayStatus = $row['status'];
                if ($row['status'] !== 'Closed' && $end < $now) {
                  $displayStatus = 'Closed';
                } elseif ($row['status'] !== 'Draft' && $start <= $now && $end >= $now) {
                  $displayStatus = 'Active';
                }
            ?>
              <tr>
                <td><?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($row['election_type'] ?: 'Not set', ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars(trim(($row['state_name'] ?: '').' / '.($row['district_name'] ?: '').' / '.($row['constituency_name'] ?: ''), ' /'), ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($row['start_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($row['end_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><span class="label label-<?php echo $displayStatus === 'Active' ? 'success' : ($displayStatus === 'Closed' ? 'default' : 'warning'); ?>"><?php echo $displayStatus; ?></span></td>
                <td>
                  <?php if($displayStatus !== 'Closed'): ?>
                    <form method="POST" action="election_status.php" style="display:inline">
                      <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                      <input type="hidden" name="status" value="<?php echo $displayStatus === 'Active' ? 'Closed' : 'Active'; ?>">
                      <button class="btn btn-xs btn-primary" type="submit"><?php echo $displayStatus === 'Active' ? 'Close' : 'Activate'; ?></button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
  <?php include 'includes/footer.php'; ?>
</div>
<?php include 'includes/scripts.php'; ?>
<div class="modal fade" id="addElection">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="election_add.php">
        <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4>Schedule Election</h4></div>
        <div class="modal-body">
          <div class="form-group"><label>Name</label><input class="form-control" name="name" required maxlength="120"></div>
          <div class="form-group"><label>Description</label><textarea class="form-control" name="description" rows="3"></textarea></div>
          <div class="form-group"><label>Start</label><input class="form-control" type="datetime-local" name="start_at" required></div>
          <div class="form-group"><label>End</label><input class="form-control" type="datetime-local" name="end_at" required></div>
          <div class="form-group"><label>Election type</label><select class="form-control" name="election_type_id" required>
            <option value="">- Select -</option>
            <?php $types = $conn->query("SELECT id,name FROM election_types ORDER BY id"); while($type = $types->fetch_assoc()): ?>
              <option value="<?php echo (int) $type['id']; ?>"><?php echo htmlspecialchars($type['name'], ENT_QUOTES, 'UTF-8'); ?></option>
            <?php endwhile; ?>
          </select></div>
          <div class="form-group"><label>State</label><select class="form-control" name="state_id" required>
            <option value="">- Select -</option>
            <?php $states = $conn->query("SELECT id,name FROM states ORDER BY name"); while($state = $states->fetch_assoc()): ?>
              <option value="<?php echo (int) $state['id']; ?>"><?php echo htmlspecialchars($state['name'], ENT_QUOTES, 'UTF-8'); ?></option>
            <?php endwhile; ?>
          </select></div>
          <div class="form-group"><label>District</label><select class="form-control" name="district_id" required>
            <option value="">- Select -</option>
            <?php $districts = $conn->query("SELECT id,name FROM districts ORDER BY name"); while($district = $districts->fetch_assoc()): ?>
              <option value="<?php echo (int) $district['id']; ?>"><?php echo htmlspecialchars($district['name'], ENT_QUOTES, 'UTF-8'); ?></option>
            <?php endwhile; ?>
          </select></div>
          <div class="form-group"><label>Constituency</label><select class="form-control" name="constituency_id" required>
            <option value="">- Select -</option>
            <?php $constituencies = $conn->query("SELECT id,name FROM constituencies ORDER BY name"); while($constituency = $constituencies->fetch_assoc()): ?>
              <option value="<?php echo (int) $constituency['id']; ?>"><?php echo htmlspecialchars($constituency['name'], ENT_QUOTES, 'UTF-8'); ?></option>
            <?php endwhile; ?>
          </select></div>
        </div>
        <div class="modal-footer"><button class="btn btn-primary" name="add" type="submit">Save Schedule</button></div>
      </form>
    </div>
  </div>
</div>
</body>
</html>
