<?php include 'header.php'; ?>

<div class="flex min-h-screen bg-[#F8FAFC]">

    <!-- SIDEBAR -->
    <?php include 'sidebar.php'; ?>

    <!-- MAIN WRAPPER -->
    <div id="mainContent"
        class="flex-1 transition-all duration-300 ">

        <!-- NAVBAR -->
        <?php include 'navbar.php'; ?>

        <!-- PAGE CONTENT -->
        <main class="pt-20 px-4 md:px-6 pb-4 ml-[var(--sidebar-width)] transition-all duration-300">
            <?= $content ?>
        </main>

    </div>

</div>