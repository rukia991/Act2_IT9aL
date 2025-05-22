<?php
include "dbconnect.php";
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: auth/login.php");
    exit();
}



$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 4;
$offset = ($page - 1) * $limit;

$searchSql = $conn->real_escape_string($search);


$countResult = $conn->query("
    SELECT COUNT(*) as total FROM cars 
    WHERE name LIKE '%$searchSql%' 
       OR category LIKE '%$searchSql%' 
       OR brand LIKE '%$searchSql%'
");
$totalCars = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalCars / $limit);


$sql = "
    SELECT * FROM cars
    WHERE name LIKE '%$searchSql%' 
       OR category LIKE '%$searchSql%' 
       OR brand LIKE '%$searchSql%'
    ORDER BY id DESC
    LIMIT $limit OFFSET $offset
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Collection</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="mycss.css">

</head>

<body>
    <nav class="navbar">
        <div class="navbar-left">
            <a href="index.php" class="nav-logo">Car Collection</a>
        </div>

        <div class="navbar-right">
            <div class="nav-account-dropdown">
                <span class="nav-account" onclick="toggleUserDropdown()">
                    <i class="fas fa-user-circle"></i>
                    <?= htmlspecialchars($_SESSION['username'] ?? 'Guest') ?>
                </span>
                <div class="logout-dropdown" id="userDropdown">
                    <?php if (isset($_SESSION['username'])): ?>
                        <a href="auth/logout.php" class="logout-button"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1>Car Collection</h1>

        <!-- Search -->
        <form action="index.php" method="GET" class="search-bar">
            <input type="text" name="search" placeholder="Search car by name, category, or brand..."
                value="<?= htmlspecialchars($search) ?>">
            <button type="submit"><i class="fas fa-search"></i> Search</button>
        </form>

        <button onclick="openModal()"><i class="fas fa-plus"></i> Add New Car</button>


        <!-- Add New Car Modal -->
        <div id="addCarModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <form action="add.php" method="POST" enctype="multipart/form-data" class="car-form">
                    <input type="text" name="name" placeholder="Car Name" required>
                    <input type="text" name="category" placeholder="Category (e.g., F1, JDM, Exotic)" required>
                    <input type="text" name="brand" placeholder="Brand (optional)">
                    <input type="file" name="image" accept="image/*">
                    <textarea name="description" placeholder="Car Description"></textarea>
                    <button type="submit" name="savit">Add</button>
                </form>
            </div>
        </div>
        <!-- This should be hidden initially -->
        <div id="editCarModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeEditModal()">&times;</span>
                <form method="POST" action="edit.php" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="edit-id">

                    <label><i class="fas fa-car-side"></i> Car Name</label>
                    <input type="text" name="name" id="edit-name" class="form-control">

                    <label><i class="fas fa-tags"></i> Category</label>
                    <input type="text" name="category" id="edit-category" class="form-control">

                    <label><i class="fas fa-industry"></i> Brand</label>
                    <input type="text" name="brand" id="edit-brand" class="form-control">

                    <label><i class="fas fa-align-left"></i> Description</label>
                    <textarea name="description" id="edit-description" class="form-control"></textarea>

                    <label><i class="fas fa-image"></i> Image</label>
                    <input type="file" name="image" id="edit-image" class="form-control">
                    <img id="edit-preview" src="" style="width:100px; display:none;">

                    <label><i class="fas fa-calendar-alt"></i> Created At</label>
                    <input type="text" name="created_at" id="edit-created-at" class="form-control" readonly>


                    <button type="submit" name="update" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>

        <div class="car-grid">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="car-card">
                    <!-- Dropdown positioned top-right -->
                    <div class="card-menu">
                        <div class="dropdown">
                            <button class="dropbtn" onclick="toggleDropdown(this)">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-content">
                                <button type="button" onclick="openEditModal(this)" data-id="<?= $row['id'] ?>"
                                    data-name="<?= htmlspecialchars($row['name']) ?>"
                                    data-category="<?= htmlspecialchars($row['category']) ?>"
                                    data-brand="<?= htmlspecialchars($row['brand']) ?>"
                                    data-description="<?= htmlspecialchars($row['description']) ?>"
                                    data-image="images/<?= htmlspecialchars($row['image']) ?>"
                                    data-created="<?= $row['created_at'] ?>">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <form action="delete.php" method="POST"
                                    onsubmit="return confirm('Are you sure you want to delete this car?');">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <button type="submit"><i class="fas fa-trash-alt"></i> Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Car image -->
                    <img src="images/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">

                    <h3><?= htmlspecialchars($row['name']) ?></h3>

                    <div class="car-info">
                        <p><i class="fas fa-tags"></i> <strong>Category:</strong> <?= htmlspecialchars($row['category']) ?>
                        </p>
                        <p><i class="fas fa-industry"></i> <strong>Brand:</strong>
                            <?= htmlspecialchars($row['brand'] ?? 'N/A') ?></p>
                        <p><i class="fas fa-align-left"></i> <?= htmlspecialchars($row['description']) ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>


        <!-- Pagination -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?search=<?= urlencode($search) ?>&page=<?= $page - 1 ?>">&laquo; Prev</a>
            <?php endif; ?>

            <?php
            $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);

            if ($start > 1) {
                echo '<a href="?search=' . urlencode($search) . '&page=1">1</a>';
                if ($start > 2)
                    echo '<span class="dots">...</span>';
            }

            for ($i = $start; $i <= $end; $i++) {
                $activeClass = ($i === $page) ? 'active' : '';
                echo '<a href="?search=' . urlencode($search) . '&page=' . $i . '" class="' . $activeClass . '">' . $i . '</a>';
            }

            if ($end < $totalPages) {
                if ($end < $totalPages - 1)
                    echo '<span class="dots">...</span>';
                echo '<a href="?search=' . urlencode($search) . '&page=' . $totalPages . '">' . $totalPages . '</a>';
            }
            ?>

            <?php if ($page < $totalPages): ?>
                <a href="?search=<?= urlencode($search) ?>&page=<?= $page + 1 ?>">Next &raquo;</a>
            <?php endif; ?>
        </div>
    </div>
    <script>
        // Toggle visibility of the form
        function toggleForm() {
            const form = document.querySelector('.car-form');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }

        // Open 'addCarModal' modal
        function openModal() {
            document.getElementById('addCarModal').style.display = 'block';
        }

        // Close 'addCarModal' modal
        function closeModal() {
            document.getElementById('addCarModal').style.display = 'none';
        }

        // Close modal when clicking outside of it
        window.onclick = function (event) {
            const modal = document.getElementById('addCarModal');
            if (event.target === modal) {
                closeModal();
            }

            const editModal = document.getElementById('editCarModal');
            if (event.target === editModal) {
                closeEditModal();
            }
        }

        function openEditModal(button) {
            document.getElementById('edit-id').value = button.dataset.id;
            document.getElementById('edit-name').value = button.dataset.name;
            document.getElementById('edit-category').value = button.dataset.category;
            document.getElementById('edit-brand').value = button.dataset.brand;
            document.getElementById('edit-description').value = button.dataset.description;
            document.getElementById('edit-created-at').value = button.dataset.created;

            const image = button.dataset.image;
            const preview = document.getElementById('edit-preview');
            if (image) {
                preview.src = image;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }

            document.getElementById('editCarModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editCarModal').style.display = 'none';
        }

        function toggleDropdown(button) {
            const dropdown = button.nextElementSibling;
            document.querySelectorAll('.dropdown-content').forEach(d => {
                if (d !== dropdown) d.style.display = 'none';
            });
            dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
        }

        document.addEventListener('click', function (e) {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-content').forEach(d => d.style.display = 'none');
            }
        });
        function toggleUserDropdown() {
            const dropdown = document.getElementById("userDropdown");
            dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
        }

        document.addEventListener("click", function (e) {
            if (!e.target.closest(".nav-account-dropdown")) {
                const dropdown = document.getElementById("userDropdown");
                if (dropdown) dropdown.style.display = "none";
            }
        });


    </script>

</body>

</html>