<div class="header-menu">
    <a href="{{ route('show-attendance') }}" class="header-menu__link">勤怠</a>
    <a href="{{ route('show-attendance-list') }}" class="header-menu__link">勤怠一覧</a>
    <a href="{{ route('show-correction-list') }}" class="header-menu__link">申請</a>
    <form action="/logout" method="post">
        @csrf
        <button class="header-menu__link">ログアウト</button>
    </form>
</div>