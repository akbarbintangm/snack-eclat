<template>
    <div class="app-shell">
        <header class="topbar">
            <div class="container-xl">
                <nav class="navbar navbar-expand-lg gap-3 px-0">
                    <RouterLink class="navbar-brand d-flex align-items-center gap-3" to="/">
                        <span class="brand-mark">SE</span>
                        <span>
                            <span class="brand-title">Snack Eclat</span>
                            <span class="brand-subtitle">Analisis Pola Penjualan Snack</span>
                        </span>
                    </RouterLink>

                    <button
                        class="navbar-toggler"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#mainNavigation"
                        aria-controls="mainNavigation"
                        aria-expanded="false"
                        aria-label="Toggle navigation"
                    >
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div id="mainNavigation" class="collapse navbar-collapse">
                        <div class="navbar-nav nav-pills ms-lg-auto">
                            <RouterLink class="nav-link" to="/">Dashboard</RouterLink>
                            <RouterLink class="nav-link" to="/transaksi">Transaksi</RouterLink>
                            <RouterLink class="nav-link" to="/analisis">Analisis</RouterLink>
                            <RouterLink class="nav-link" to="/dokumentasi">Dokumentasi</RouterLink>
                        </div>
                    </div>
                </nav>
            </div>
        </header>

        <main class="page-wrap">
            <RouterView />
        </main>
    </div>
</template>
