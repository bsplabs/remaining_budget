<header class="header" id="header">
  <div class="header__toggle">
    <!-- <i class='bx bx-menu' id="header-toggle"></i> -->
    <i class="bx bxs-right-arrow" id="header-toggle"></i>
    <!-- <i class="bx bxs-left-arrow-circle" ></i> -->
    <!-- <i class='bx bxs-right-arrow' ></i> -->
  </div>
</header>

<div class="l-navbar" id="nav-bar">
  <nav class="nav">
    <div>
      <a href="#" class="nav__logo">
        <span class="nav__logo-name short" id="nav_logo_short">RB</span>
        <span class="nav__logo-name full" id="nav_logo_full">Remaining Budget</span>
      </a>

      <div class="nav__list">
        <a href="<?php echo BASE_URL; ?>/" class="nav__link <?php echo ($data["page_name"] == false | $data['page_name'] === 'reports') ? 'active' : ''; ?>">
          <i class='bx bxs-report'></i>
          <span class="nav__name">Report</span>
        </a>

        <a href="<?php echo BASE_URL; ?>/customers" class="nav__link <?php echo ($data['page_name'] === 'customers') ? 'active' : ''; ?>">
          <i class='bx bx-user nav__icon'></i>
          <span class="nav__name">Customer</span>
        </a>
      </div>
      
    </div>
  </nav>
</div>