<nav class="sidebar">
  <div class="sidebar-header">
    <a href="#" class="sidebar-brand">
      Web<span>App</span>
    </a>
    <div class="sidebar-toggler not-active">
      <span></span>
      <span></span>
      <span></span>
    </div>
  </div>
  <div class="sidebar-body">
    <ul class="nav">
      {{-- @if ()
          
      @endif --}}
      <li class="nav-item nav-category">Main</li>
      <li class="nav-item {{ active_class(['/']) }}">
        <a href="{{ url('/main-dashboard') }}" class="nav-link">
          <i class="link-icon" data-feather="box"></i>
          <span class="link-title">Dashboard</span>
        </a>
      </li>
      @if(Auth::guard('usersSecond')->user()->role === 10)
      {{-- Account Management --}}
      {{-- <li class="nav-item nav-category">Manage users account</li> --}}
      <li class="nav-item {{ active_class(['ui-components/*']) }}">
        <a class="nav-link" data-bs-toggle="collapse" href="#accountManagement" role="button" aria-expanded="{{ is_active_route(['ui-components/*']) }}" aria-controls="uiComponents">
          <i class="link-icon" data-feather="tool"></i>
          <span class="link-title">Settings</span>
          <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="collapse {{ show_class(['ui-components/*']) }}" id="accountManagement">
          <ul class="nav sub-menu">
            <li class="nav-item">
              <a href="{{ route('ApprovalConfiguration') }}" class="nav-link {{ active_class(['ui-components/accordion']) }}">Approval Configuration</a>
            </li>
            {{-- <li class="nav-item">
              <a href="{{ url('/ui-components/alerts') }}" class="nav-link {{ active_class(['ui-components/alerts']) }}">Permissions</a>
            </li>
            <li class="nav-item">
              <a href="{{ url('/ui-components/badges') }}" class="nav-link {{ active_class(['ui-components/badges']) }}">Roles</a>
            </li> --}}
          </ul>
        </div>
      </li>

      @else
      <li class="nav-item {{ active_class(['request/*']) }}">
        <a class="nav-link" data-bs-toggle="collapse" href="#uiComponents" role="button" aria-expanded="{{ is_active_route(['ui-components/*']) }}" aria-controls="uiComponents">
          <i class="link-icon" data-feather="tool"></i>
          <span class="link-title">Request</span>
          <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="collapse {{ show_class(['request/*']) }}" id="uiComponents">
          <ul class="nav sub-menu">
            {{-- <li class="nav-item">
              <a href="{{ url('/') }}" class="nav-link {{ active_class(['ui-components/accordion']) }}">Preventive Maintenance</a>
            </li>
            <li class="nav-item">
              <a href="{{ url('/') }}" class="nav-link {{ active_class(['ui-components/alerts']) }}">Corrective Maintenance</a>
            </li> --}}
            <li class="nav-item">
              <a href="{{route('eh.addWorkOrder')}}" class="nav-link {{ active_class(['request/equipment-handling-work-order']) }}">Equipment Handling</a>
            </li>
          </ul>
        </div>
      </li>

      @endif
      

    </ul>
  </div>
</nav>