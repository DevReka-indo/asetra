<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="fw-bold mb-0">Pelaksanaan Stock Opname</h3>

    <ul
        class="breadcrumbs d-flex align-items-center p-0 m-0"
        style="list-style: none;"
    >
        <li class="nav-home d-flex align-items-center">
            <a
                href="{{ url('dashboard') }}"
                class="text-muted text-decoration-none d-flex align-items-center"
            >
                <i class="fas fa-home me-2" style="font-size: 15px;"></i>
                <span
                    style="font-size: 14px; font-weight: 500; position: relative; top: 2px;"
                >
                    Dashboard
                </span>
            </a>
        </li>

        <li class="separator text-muted d-flex align-items-center px-2">
            <span style="font-size: 14px; position: relative; top: 2px;">-</span>
        </li>

        <li class="nav-item d-flex align-items-center">
            <a
                href="{{ route('stock-opname.user-index') }}"
                class="text-muted text-decoration-none d-flex align-items-center"
            >
                <span
                    style="font-size: 14px; font-weight: 500; position: relative; top: 2px;"
                >
                    Pelaksanaan Stock Opname
                </span>
            </a>
        </li>

        <li class="separator text-muted d-flex align-items-center px-2">
            <span style="font-size: 14px; position: relative; top: 2px;">-</span>
        </li>

        <li class="nav-item">
            <span style="font-size: 14px; position: relative; top: 2px;">
                {{ $session->periode }}
            </span>
        </li>
    </ul>
</div>

@if(session('success'))
    <div
        class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3"
        role="alert"
    >
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
            aria-label="Tutup"
        ></button>
    </div>
@endif
