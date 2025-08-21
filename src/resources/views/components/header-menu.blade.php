<div class="header-menu">
    @if(!Auth::user()->is_admin)
        <!-- 管理者権限メニュー -->
        <a href="{{ route('show-attendance') }}" class="header-menu__link">勤怠</a>
        <a href="{{ route('show-attendance-list') }}" class="header-menu__link">勤怠一覧</a>
        <a href="{{ route('show-correction-list') }}" class="header-menu__link">申請</a>
    @else
        <!-- 一般ユーザーメニュー -->
        <a href="{{ route('admin-show-attendance-list') }}" class="header-menu__link">勤怠一覧</a>
        <a href="" class="header-menu__link">スタッフ一覧</a>
        <a href="" class="header-menu__link">申請一覧</a>
    @endif
    <form action="/logout" method="post">
        @csrf
        <button class="header-menu__link">ログアウト</button>
    </form>
</div>