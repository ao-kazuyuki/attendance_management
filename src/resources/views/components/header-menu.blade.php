<div class="header-menu">
    <span class="header-menu__link">勤怠</span>
    <span class="header-menu__link">勤怠一覧</span>
    <span class="header-menu__link">申請</span>
    <form action="/logout" method="post">
        @csrf
        <button class="header-menu__link">ログアウト</button>
    </form>
</div>