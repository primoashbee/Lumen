<nav id="navbar" class="navbar">
  <div class="sidebar-toggle">
    <button class="btn ml-3" id="sidebar-toggle">
      <i class="fas fa-list visible-on-sidebar-regular" id="regular"></i>
      <i class="fas fa-align-center" id="mini"></i>
    </button>
    
    <div class="logo-container">
    <img src="{{ asset('assets/img/logo.png')}}">
    </div>
    <a class="navbar-brand l-text float-left" href="#">Create Client</a>

  </div>
  <div>
  
  <ul class="nav nav-pills">
    <li>
      <a href="" data-target="#search_bar" id="search_icon" data-toggle="modal">
        <i class="fas fa-search"></i>
      </a>
    </li>
    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle l-text" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">{{auth()->user()->name()}}</a>
      <div class="dropdown-menu">
        <ul>
          <li>
          <a class="dropdown-item d-text" href="#one">Profile</a>
          </li>
          <li class="">
          <a class="dropdown-item d-text" href="#two">Logout</a>
          </li>
        </ul>
      </div>
    </li>
  </ul>
</nav>
<div class="modal fade modal-search" id="search_bar" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <input type="text" class="form-control search_text" placeholder="SEARCH" name="">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      </div>
    </div>
  </div>