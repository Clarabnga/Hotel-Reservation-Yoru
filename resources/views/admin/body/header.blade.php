<nav class="navbar navbar-expand-lg">
    <div class="navbar-content">
      
        <ul class="navbar-nav">
            <li class="nav-item dropdown">
                <button class="nav-link dropdown-toggle border-0 bg-transparent" id="profileDropdown" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="wd-30 ht-30 rounded-circle d-grid place-items-center bg-primary text-white">{{ Str::upper(Str::substr(Auth::user()->name ?: 'A',0,1)) }}</span>
                </button>
                <div class="dropdown-menu p-0" aria-labelledby="profileDropdown">
                    <div class="d-flex flex-column align-items-center border-bottom px-5 py-3">
                        <div class="text-center">
                            <p class="tx-16 fw-bolder">{{Auth::user()->name}}</p>
                            <p class="tx-12 text-muted">{{Auth::user()->name}}</p>
                        </div>
                    </div>
    <ul class="list-unstyled p-1">
      <li class="dropdown-item py-2">
        <a href="{{url('/admin/profile')}}" class="text-body ms-0">
          <i class="me-2 icon-md" data-feather="user"></i>
          <span>Profile</span>
        </a>
      </li>
      <li class="dropdown-item py-2">
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="btn text-body ms-0 p-0">
            <i class="me-2 icon-md" data-feather="log-out"></i>
            <span>Log Out</span>
          </button>
        </form>
      </li>
    </ul>
                </div>
            </li>
        </ul>
    </div>
</nav>
