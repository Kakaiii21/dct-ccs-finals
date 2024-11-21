<div class="sidebar border border-right col-md-3 col-lg-2 p-0 bg-body-tertiary vh-100">
    <div class="offcanvas-md offcanvas-end bg-body-tertiary" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarMenuLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="sidebarMenuLabel">Company name</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body d-md-flex flex-column p-0 pt-lg-3 overflow-y-auto">
            <ul class="nav flex-column">
                <li class="nav-item">
                <a class="nav-link d-flex align-items-center gap-2" href="/admin/dashboard.php" style="font-weight: <?php echo ($_SERVER['PHP_SELF'] == '/admin/dashboard.php' ? 'bold' : 'normal'); ?>;">
                        <i class="fa-solid fa-gauge fa-fw me-2"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                <a class="nav-link d-flex align-items-center gap-2" href="/admin/subject/add.php" style="font-weight: <?php echo ($_SERVER['PHP_SELF'] == '/admin/subject/add.php' || $_SERVER['PHP_SELF'] == '/admin/subject/edit.php' || $_SERVER['PHP_SELF'] == '/admin/subject/delete.php' ? 'bold' : 'normal'); ?>;">
                    <i class="fa-solid fa-file fa-fw me-2"></i>
                        Subjects
                    </a>
                </li>
                <li class="nav-item">
                <a class="nav-link d-flex align-items-center gap-2" href="/admin/student/register.php" style="font-weight: <?php echo (in_array($_SERVER['PHP_SELF'], ['/admin/student/register.php', '/admin/student/attach-subject.php', '/admin/student/detach-subject.php', '/admin/student/assign-grade.php', '/admin/student/edit.php', '/admin/student/delete.php']) ? 'bold' : 'normal'); ?>;">
                        <i class="fa-solid fa-user fa-fw me-2"></i>
                        Students
                    </a>
                </li>                
            </ul>
           
            <hr class="my-3">

            <ul class="nav flex-column mb-auto">
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2" href="#">
                    <i class="fa-solid fa-right-to-bracket fa-fw me-2"></i>                        
                        Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>