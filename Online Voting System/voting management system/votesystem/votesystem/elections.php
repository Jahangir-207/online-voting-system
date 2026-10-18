<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">
  <?php include 'includes/navbar.php'; ?>
  <div class="content-wrapper" style="background-color:#F1E9D2">
    <div class="container">
      <section class="content">
        <h1 class="page-header text-center title"><b>ELIGIBLE ELECTIONS</b></h1>
        <div class="row">
          <div class="col-sm-10 col-sm-offset-1">
            <?php
              $sql = "SELECT e.*, et.name AS election_type, s.name AS state_name, d.name AS district_name, c.name AS constituency_name
                FROM elections e
                INNER JOIN election_types et ON et.id = e.election_type_id
                INNER JOIN states s ON s.id = e.state_id
                INNER JOIN districts d ON d.id = e.district_id
                INNER JOIN constituencies c ON c.id = e.constituency_id
                INNER JOIN voters v ON v.state_id = e.state_id AND v.district_id = e.district_id AND v.constituency_id = e.constituency_id
                WHERE v.id = ".(int) $voter['id']."
                ORDER BY e.start_at DESC, e.id DESC";
              $query = $conn->query($sql);
              if (!$query || $query->num_rows === 0) {
                echo '<div class="alert alert-warning">No elections are available for your registered area.</div>';
              }
              while ($query && $row = $query->fetch_assoc()) {
                $status = htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8');
                echo '<div class="box box-solid" style="background-color:#d8d1bd"><div class="box-body">';
                echo '<h3>'.htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8').'</h3>';
                echo '<p>'.htmlspecialchars($row['election_type'].' - '.$row['state_name'].' / '.$row['district_name'].' / '.$row['constituency_name'], ENT_QUOTES, 'UTF-8').'</p>';
                echo '<p>'.htmlspecialchars($row['start_at'].' to '.$row['end_at'], ENT_QUOTES, 'UTF-8').' | <b>'.$status.'</b></p>';
                echo '<a class="btn btn-primary" href="home.php">Open voter portal</a>';
                echo '</div></div>';
              }
            ?>
          </div>
        </div>
      </section>
    </div>
  </div>
  <?php include 'includes/footer.php'; ?>
</div>
<?php include 'includes/scripts.php'; ?>
</body>
</html>
