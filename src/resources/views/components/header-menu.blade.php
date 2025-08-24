<div class="header-menu">
    @if(!Auth::user()->is_admin)
        <!-- 一般ユーザーメニュー -->
        <a href="{{ route('show-attendance') }}" class="header-menu__link">勤怠</a>
        <a href="{{ route('show-attendance-list') }}" class="header-menu__link">勤怠一覧</a>
        <a href="{{ route('general-show-correction-list') }}" class="header-menu__link">申請</a>
        <form action="{{route('logout')}}" method="post">
            @csrf
            <button class="header-menu__link">ログアウト</button>
        </form>
    @else
        <!-- 管理者権限メニュー -->
        <a href="{{ route('admin-show-attendance-list') }}" class="header-menu__link">勤怠一覧</a>
        <a href="{{ route('show-staff-list') }}" class="header-menu__link">スタッフ一覧</a>
        <a href="{{ route('admin-show-correction-list') }}" class="header-menu__link">申請一覧</a>
        <form action="{{route('admin-logout')}}" method="post">
            @csrf
            <button class="header-menu__link">ログアウト</button>
        </form>
    @endif
</div>