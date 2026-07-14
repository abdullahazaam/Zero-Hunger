<?php
include 'db.php';
include 'header.php';

// DELETE DELIVERY LOG
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM deliveries WHERE delivery_id=$id");
    header("Location: deliveries.php");
    exit();
}

// UPDATE DELIVERY ASSIGNMENT & STATUS
if (isset($_POST['updateDelivery'])) {
    $delivery_id = intval($_POST['edit_delivery_id']);
    $volunteer_id = !empty($_POST['volunteer_id']) ? intval($_POST['volunteer_id']) : null;
    $status = $_POST['status'];
    $pickup = !empty($_POST['pickup_time']) ? $_POST['pickup_time'] : null;
    $delivery = !empty($_POST['delivery_time']) ? $_POST['delivery_time'] : null;

    $stmt = $conn->prepare("UPDATE deliveries SET volunteer_id=?, pickup_time=?, delivery_time=?, status=? WHERE delivery_id=?");
    $stmt->bind_param("isssi", $volunteer_id, $pickup, $delivery, $status, $delivery_id);
    
    if($stmt->execute()) {
        // Agar delivery complete ho jaye tou core donation status ko update kar dein
        if($status == 'Completed') {
            $res = mysqli_query($conn, "SELECT request_id FROM deliveries WHERE delivery_id=$delivery_id");
            $dData = mysqli_fetch_assoc($res);
            $reqId = $dData['request_id'];
            mysqli_query($conn, "UPDATE food_donations fd JOIN requests r ON fd.donation_id=r.donation_id SET fd.status='Delivered' WHERE r.request_id=$reqId");
        }
        header("Location: deliveries.php");
        exit();
    }
    $stmt->close();
}

$sql = "SELECT d.*, r.request_id, u_ngo.full_name AS ngo_name, fd.food_item, fd.quantity, u_vol.full_name AS volunteer_name 
        FROM deliveries d
        JOIN requests r ON d.request_id = r.request_id
        JOIN food_donations fd ON r.donation_id = fd.donation_id
        JOIN users u_ngo ON r.receiver_id = u_ngo.user_id
        LEFT JOIN users u_vol ON d.volunteer_id = u_vol.user_id
        ORDER BY d.delivery_id DESC";
$result = mysqli_query($conn, $sql);
?>

<div class="container my-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Food Delivery Dispatches</h3>
  </div>

  <div class="table-responsive bg-white shadow-sm p-3 rounded">
    <table class="table table-hover align-middle text-center">
      <thead class="table-light">
        <tr>
          <th>Delivery ID</th>
          <th>Req ID</th>
          <th>NGO Target</th>
          <th>Food Item</th>
          <th>Qty</th>
          <th>Volunteer</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): 
            $vName = $row['volunteer_name'] ?? "<span class='text-danger'>Not Assigned</span>";
            $badge = ($row['status'] == 'Completed') ? 'bg-success' : (($row['status'] == 'In Transit') ? 'bg-info' : 'bg-warning text-dark');
        ?>
        <tr>
          <td><?= $row['delivery_id'] ?></td>
          <td><?= $row['request_id'] ?></td>
          <td><strong><?= htmlspecialchars($row['ngo_name']) ?></strong></td>
          <td><?= htmlspecialchars($row['food_item']) ?></td>
          <td><?= htmlspecialchars($row['quantity']) ?></td>
          <td><?= $vName ?></td>
          <td><span class="badge <?= $badge ?>"><?= $row['status'] ?></span></td>
          <td>
            <button class="btn btn-sm btn-outline-primary editDelBtn" 
                    data-id="<?= $row['delivery_id'] ?>"
                    data-volunteer="<?= $row['volunteer_id'] ?>"
                    data-pickup="<?= $row['pickup_time'] ?>"
                    data-delivery="<?= $row['delivery_time'] ?>"
                    data-status="<?= $row['status'] ?>"><i class="bi bi-pencil-square"></i></button>
            <a href="deliveries.php?delete=<?= $row['delivery_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this record?');"><i class="bi bi-trash"></i></a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="editDeliveryModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-3">
      <form method="POST">
        <input type="hidden" name="edit_delivery_id" id="edit_delivery_id">
        <h5 class="mb-3 text-center">Update Dispatch Dispatch</h5>
        
        <label class="small text-muted mb-1">Assign Volunteer</label>
        <select name="volunteer_id" id="edit_volunteer_id" class="form-control mb-2">
            <option value="">Select Rider / Volunteer</option>
            <?php
            $vols = mysqli_query($conn, "SELECT user_id, full_name FROM users WHERE role_id = 4");
            while($v = mysqli_fetch_assoc($vols)) echo "<option value='{$v['user_id']}'>{$v['full_name']}</option>";
            ?>
        </select>

        <label class="small text-muted mb-1">Pickup Time</label>
        <input type="datetime-local" name="pickup_time" id="edit_pickup_time" class="form-control mb-2">

        <label class="small text-muted mb-1">Delivery Drop Time</label>
        <input type="datetime-local" name="delivery_time" id="edit_delivery_time" class="form-control mb-2">

        <label class="small text-muted mb-3">Status</label>
        <select name="status" id="edit_status" class="form-control mb-3" required>
          <option value="Pending Assignment">Pending Assignment</option>
          <option value="In Transit">In Transit</option>
          <option value="Completed">Completed</option>
          <option value="Failed">Failed</option>
        </select>
        <button type="submit" name="updateDelivery" class="btn btn-success w-100">Update Dispatch</button>
      </form>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.editDelBtn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('edit_delivery_id').value = this.dataset.id;
        document.getElementById('edit_volunteer_id').value = this.dataset.volunteer;
        if(this.dataset.pickup) document.getElementById('edit_pickup_time').value = this.dataset.pickup.replace(" ", "T").substring(0,16);
        if(this.dataset.delivery) document.getElementById('edit_delivery_time').value = this.dataset.delivery.replace(" ", "T").substring(0,16);
        document.getElementById('edit_status').value = this.dataset.status;
        new bootstrap.Modal(document.getElementById('editDeliveryModal')).show();
    });
});
</script>

<?php include 'footer.php'; ?>