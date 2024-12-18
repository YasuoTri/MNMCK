<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="<?=ADMIN_URL?>/index.php" class="brand-link">
        <img src="../assets/img/bht_bookstore_logo.png" alt="BHT Bookstore" style="width: 100%">
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->


        <!-- SidebarSearch Form -->
        <div class="form-inline">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="Tìm kiếm"
                    aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-sidebar">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2" style="height: calc(100% - 74px)">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                <li class="nav-item">
                    <a href="<?=ADMIN_URL?>/categories.php" class="nav-link">
                        <i class="nav-icon fas fa-clipboard-list"></i>
                        <p>Thể loại</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?=ADMIN_URL?>/authors.php" class="nav-link">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Tác giả</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?=ADMIN_URL?>/publishes.php" class="nav-link">
                        <i class="nav-icon fas fa-print"></i>
                        <p>Nhà xuất bản</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?=ADMIN_URL?>/books.php" class="nav-link">
                        <i class="nav-icon fas fa-book"></i>
                        <p>Sách</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?=ADMIN_URL?>/suppliers.php" class="nav-link">
                        <i class="nav-icon fas fa-boxes"></i>
                        <p>Nhà cung cấp</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?=ADMIN_URL?>/orders.php" class="nav-link">
                        <i class="nav-icon fas fa-truck-loading"></i>
                        <p>Đơn hàng</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?=ADMIN_URL?>/users.php" class="nav-link">
                        <i class="nav-icon fas fa-user-circle"></i>
                        <p>Tài khoản</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?=ADMIN_URL?>/sliders.php" class="nav-link">
                        <i class="nav-icon fas fa-images"></i>
                        <p>Slider</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../index.php" class="nav-link">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>Trang mua hàng</p>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>