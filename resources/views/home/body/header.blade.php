<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
      <!-- Navbar Toggle Button for Mobile -->
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
      </button>

      <!-- Navbar Links (Centered) -->
      <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
          <ul class="navbar-nav">
              <li class="nav-item">
                  <a class="nav-link active" href="{{url('/')}}">Home</a>
              </li>
              <li class="nav-item">
                  <a class="nav-link" href="{{url('/home/facilities')}}">Facilities</a>
              </li>
              <li class="nav-item">
                  <a class="nav-link" href="{{url('/our-rooms')}}"> Our Rooms</a>
              </li>
              <li class="nav-item">
                  <a class="nav-link" href="#">Contact</a>
              </li>
          </ul>
      </div>

      <!-- Right Section: Search Bar & User Dropdown -->
      <div class="d-flex align-items-center">
          <!-- Search Form -->
          <form class="d-flex me-3">
              <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
              <button class="btn btn-outline-light" type="submit">Search</button>
          </form>

          <!-- User Profile & Logout Dropdown -->
          <div class="dropdown">
              <button class="btn btn-outline-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="icon-md" data-feather="user"></i> Account
              </button>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                  <li>
                      <a class="dropdown-item" href="pages/general/profile.html">
                          <i class="me-2 icon-md" data-feather="user"></i> Profile
                      </a>
                  </li>
                  <li>
                      <a class="dropdown-item text-danger" href="{{route('home.logout')}}">
                          <i class="me-2 icon-md" data-feather="log-out"></i> Log Out
                      </a>
                  </li>
              </ul>
          </div>
      </div>
  </div>

  
</nav>
