<!doctype html>
<html lang="ru">
<head>
    <title>Авторизация</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, interactive-widget=resizes-content" />
    <link rel="shortcut icon" href="/html/assets/favicons/favicon.ico" />
    <link rel="icon" href="/html/assets/favicons/favicon.svg" type="image/svg+xml" />
    <link rel="icon" href="/html/assets/favicons/favicon-96x96.png" type="image/png" sizes="96x96" />
    <link rel="icon" href="/html/assets/favicons/favicon-192x192.png" type="image/png" sizes="192x192" />
    <link rel="icon" href="/html/assets/favicons/favicon-512x512.png" type="image/png" sizes="512x512" />
    <link rel="apple-touch-icon" href="/html/assets/favicons/apple-touch-icon.png" sizes="180x180" />
    <link rel="stylesheet" href="{{ asset('css/bundle.min.css') }}">
</head>
<body>
<div class="site">
    <main class="main">
        <section class="auth">
            <div class="block">
                <a class="logo" href="./home.html">
                    <picture>
                        <img src="/html/assets/images/system/logo.svg" alt="" />
                    </picture>
                </a>

                <h1 class="title">Ру Класс — интерактивная образовательная онлайн-платформа</h1>

                <form class="form" method="POST" action="/login">
                    @csrf
                    <div class="title">Авторизация</div>

                    <div class="grid">
                        <div class="field">
                            <label class="label" for="auth-email"> Email</label>

                            <div class="control-row">
                                <input type="email" class="input primary size-md rounded-sm" id="auth-email" name="email" placeholder="Укажите ваш почтовый ящик" autocomplete="email" />
                            </div>
                        </div>

                        <div class="field">
                            <label class="label" for="auth-password"> Пароль</label>

                            <div class="control-row">
                                <input type="password" class="input primary size-md rounded-sm" id="auth-password" name="password" placeholder="••••••••" autocomplete="current-password" />
                            </div>
                        </div>

                        <div class="actions">
                            <button class="button primary size-md rounded-sm is-disabled" type="submit" disabled>
                                <span>Войти</span>
                            </button>
                            <div>
                                <a href="#">Забыли пароль?</a>
                                <a href="/register">Регистрация</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <div class="menu" data-dropdown="menu">
        <div class="inner">
            <div class="menu-header">
                <span class="title"> Меню </span>
                <button class="menu-close" type="button" data-toggle="menu">
                    <svg width="15" height="20" viewBox="0 0 15 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M2.15234 2.86719C1.66406 2.37891 0.871094 2.37891 0.382812 2.86719C-0.105469 3.35547 -0.105469 4.14844 0.382812 4.63672L5.75 10L0.386719 15.3672C-0.101563 15.8555 -0.101563 16.6484 0.386719 17.1367C0.875 17.625 1.66797 17.625 2.15625 17.1367L7.51953 11.7695L12.8867 17.1328C13.375 17.6211 14.168 17.6211 14.6563 17.1328C15.1445 16.6445 15.1445 15.8516 14.6563 15.3633L9.28906 10L14.6523 4.63281C15.1406 4.14453 15.1406 3.35156 14.6523 2.86328C14.1641 2.375 13.3711 2.375 12.8828 2.86328L7.51953 8.23047L2.15234 2.86719Z"
                            fill="currentColor"
                        />
                    </svg>
                </button>
            </div>
            <div class="menu-navigation">
                <nav class="navigation-mobile">
                    <ul>
                        <li class="has-submenu">
                            <div class="row">
                                <a href="#">
                                    <span>Расписание школы</span>
                                </a>
                                <button class="toggle" type="button" aria-label="Открыть подменю" aria-expanded="false">
                                    <svg width="15" height="9" viewBox="0 0 15 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M6.61719 8.38574C7.10547 8.87402 7.89844 8.87402 8.38672 8.38574L14.6367 2.13574C15.125 1.64746 15.125 0.854492 14.6367 0.366211C14.1484 -0.12207 13.3555 -0.12207 12.8672 0.366211L7.5 5.7334L2.13281 0.370117C1.64453 -0.118164 0.851562 -0.118164 0.363281 0.370117C-0.125 0.858398 -0.125 1.65137 0.363281 2.13965L6.61328 8.38965L6.61719 8.38574Z"
                                            fill="currentColor"
                                        />
                                    </svg>
                                </button>
                            </div>
                            <div class="wrap">
                                <ul class="submenu">
                                    <li>
                                        <a href="./schedule-day.html"> Расписание школы на день </a>
                                    </li>

                                    <li>
                                        <a href="./schedule-week.html"> Расписание школы на неделю </a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        <li class="">
                            <a href="./groups.html">
                                <span>Список групп</span>
                            </a>
                        </li>

                        <li class="">
                            <a href="./courses.html">
                                <span>Каталог курсов</span>
                            </a>
                        </li>

                        <li class="">
                            <a href="./glossary.html">
                                <span>Глоссарий</span>
                            </a>
                        </li>

                        <li class="">
                            <a href="./teachers.html">
                                <span>Педагогический состав</span>
                            </a>
                        </li>

                        <li class="">
                            <a href="./students.html">
                                <span>Учащиеся</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            <div class="menu-navigation">
                <nav class="navigation-mobile small">
                    <ul>
                        <li class="">
                            <a href="./schedule-personal.html">
                                <span>Мое расписание</span>
                            </a>
                        </li>

                        <li class="">
                            <a href="./groups-personal.html">
                                <span>Мои группы</span>
                            </a>
                        </li>

                        <li class="">
                            <a href="./statistics.html">
                                <span>Статистика</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
    <div class="overlay" aria-hidden="true"></div>
</div>
<script src="/html/js/bundle.min.js"></script>
</body>
</html>
