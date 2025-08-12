<div class="header-menu">
    <a href="{{ route('show-attendance') }}" class="header-menu__link">勤怠</あ>
    <a href="{{ route('show-attendance-list') }}" class="header-menu__link">勤怠一覧</a>
    <span class="header-menu__link">申請</span>
    <form action="/logout" method="post">
        @csrf
        <button class="header-menu__link">ログアウト</button>
    </form>
</div>