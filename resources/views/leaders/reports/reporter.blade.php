@extends('layouts.leader')
@section('content')
    <header class="header-2">
        <div class="page-header min-vh-35 relative" style="background-image: url('{{ asset('assets/img/bg10.jpg') }}')">
            <span class="mask bg-gradient-dark opacity-4"></span>
            <div class="container">
                <div class="row">
                    <div class="col-12 mx-auto">
                        <h3 class="text-white pt-3 mt-n2">Training</h3>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="card card-body blur shadow-blur mx-3 mx-md-4 mt-n6">

        <section class="pt-3 pb-4" id="count-stats">
            <div class="container">
                @if ($errors->any())
                    <div class="row">
                        @foreach ($errors->all() as $error)
                            <div class="col-12 col-lg-6">
                                <div class="alert alert-danger text-white text-xs alert-dismissible fade show"
                                    role="alert">
                                    {{ $error }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <h5 class="text-center text-secondary mb-4">List of Training Reports in : <span
                        class="text-primary">{{ $month }} - {{ $year }}</span></h5>

                <!-- Tombol Add -->
                <button class="btn btn-primary mx-3" data-bs-toggle="modal" data-bs-target="#addReportModal">
                    <span style="padding-left: 50px; padding-right: 50px;"><b>+</b> Add</span>
                </button>

                <div class="table-responsive p-0">
                    <table id="example" class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">No
                                </th>
                                <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">
                                    Start Training</th>
                                <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">
                                    Name Member</th>
                                <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7"
                                    style="width: 15%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reports as $report)
                                <tr>
                                    <td class="align-middle text-center">
                                        <p class="text-xs font-weight-bold text-secondary">{{ $loop->iteration }}</p>
                                    </td>
                                    <td class="align-middle text-center">
                                        <p class="text-xs text-primary mb-0">
                                            {{ \Carbon\Carbon::parse($report->Start_Report)->format('d-m-Y') }}</p>
                                    </td>
                                    <td class="align-middle text-center">
                                        <p class="text-xs text-primary mb-0">{{ $report->member->Name_Member }}</p>
                                    </td>
                                    <td class="align-middle text-center">
                                        <div class="d-flex justify-content-center">
                                            <a href="{{ route('list_report', ['Id_Report' => $report->Id_Report]) }}"
                                                class="text-primary text-xs mx-1" title="View Details">
                                                <i class="material-symbols-rounded">app_registration</i>
                                            </a>
                                            <!-- Tombol Edit -->
                                            <a href="#" class="text-warning text-xs mx-1 edit-btn" title="Edit"
                                                data-id="{{ $report->Id_Report }}" data-start="{{ $report->Start_Report }}"
                                                data-members="{{ $report->Id_Member }}">
                                                <i class="material-symbols-rounded">edit</i>
                                            </a>
                                            <!-- Tombol Delete -->
                                            <form action="{{ route('reporter.destroy', $report->Id_Report) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to delete this report?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="border-0 bg-transparent p-0 text-xs mx-1"
                                                    title="Delete">
                                                    <i class="text-danger material-symbols-rounded">delete</i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

    <!-- Modal Add Report -->
    <div class="modal fade" id="addReportModal" tabindex="-1" aria-labelledby="addReportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('reporter.create') }}" role="form" method="POST">
                    @csrf
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title text-white" id="addReportModalLabel">Add Report</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="input-group input-group-outline my-3 is-filled">
                            <label class="form-label">Start Training</label>
                            <input type="date" class="form-control" name="Start_Report" required>
                        </div>
                        <div class="input-group input-group-outline my-3 is-filled">
                            <label class="form-label">Members</label>
                            <select class="form-control select2" name="Id_Member[]" multiple required>
                                @foreach ($members as $member)
                                    <option value="{{ $member->Id_Member }}">{{ $member->Name_Member }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn bg-gradient-primary w-100 my-2">Submit</button>
                        <button type="button" class="btn bg-gradient-secondary w-100 my-2 mb-2"
                            data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Report -->
    <div class="modal fade" id="editReportModal" tabindex="-1" aria-labelledby="editReportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="editReportForm" role="form" method="POST">
                    @csrf
                    @method('PUT') <!-- Gunakan method PUT untuk update -->
                    <div class="modal-header bg-warning"> <!-- Ganti warna header menjadi warning -->
                        <h5 class="modal-title text-white" id="editReportModalLabel">Edit Report</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="Id_Report" id="edit_Id_Report">
                        <div class="input-group input-group-outline my-3 is-filled">
                            <label class="form-label">Start Training</label>
                            <input type="date" class="form-control" name="Start_Report" id="edit_Start_Report"
                                required>
                        </div>
                        <div class="input-group input-group-outline my-3 is-filled">
                            <label class="form-label">Members</label>
                            <select class="form-control select2" name="Id_Member" id="edit_Id_Member" required>
                                <!-- Edit hanya satu member -->
                                @foreach ($members as $member)
                                    <option value="{{ $member->Id_Member }}">{{ $member->Name_Member }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn bg-gradient-warning w-100 my-2">Update</button>
                        <!-- Ganti warna tombol -->
                        <button type="button" class="btn bg-gradient-secondary w-100 my-2 mb-2"
                            data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


@section('style')
    <link href="{{ asset('assets/datatables/datatables.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet">
@endsection

@section('script')
    <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script>
        new DataTable('#example');
    </script>
    <script>
        $('#addReportModal').on('shown.bs.modal', function() {
            // Untuk select dengan name="Id_Member[]"
            $('select[name="Id_Member[]"]').select2({
                dropdownParent: $('#addReportModal'),
                placeholder: "Select Members",
                allowClear: true
            });

            // Tambahkan class form-control ke Select2 input yang terlihat
            $('.select2').each(function() {
                let $select = $(this);
                let $select2Container = $select.next('.select2-container');
                $select2Container.find('.select2-selection').addClass('form-control');
            });
        });
    </script>

    <script>
        // ... (script DataTable, Select2 add, dll) ...

        // Script untuk Edit Modal
        $(document).on('click', '.edit-btn', function() {
            var reportId = $(this).data('id');
            var reportStart = $(this).data('start'); // Contoh: "2025-10-20 00:00:00" atau "2025-10-20"
            var reportMemberId = $(this).data('members'); // Contoh: "123"

            // Isi form edit dengan data dari tombol
            $('#edit_Id_Report').val(reportId);

            // 1. Untuk input tanggal: Ambil bagian tanggal saja jika format lengkap, lalu set properti 'value'
            // Format Carbon di blade mungkin: d-m-Y, jadi kita perlu pastikan format yang diterima input date: Y-m-d
            // Jika format dari data('start') adalah Y-m-d (tanpa waktu), kita bisa langsung gunakan.
            // Jika format dari data('start') adalah Y-m-d H:i:s, kita tetap bisa ambil bagian depannya.
            var dateOnly = reportStart.split(' ')[
            0]; // Ambil bagian tanggal dari "Y-m-d H:i:s" atau gunakan "Y-m-d" jika tanpa waktu
            // Set nilai ke properti DOM input date
            $('#edit_Start_Report')[0].value = dateOnly; // [0] mengakses elemen DOM asli, lalu set properti 'value'

            // 2. Untuk Select2 Member: Gunakan val() dan trigger('change')
            $('#edit_Id_Member').val(reportMemberId).trigger(
            'change.select2'); // Gunakan 'change.select2' untuk Select2 secara eksplisit

            // Set action form untuk update
            $('#editReportForm').attr('action', '{{ route('reporter.update', ':id') }}'.replace(':id', reportId));

            // Tampilkan modal
            $('#editReportModal').modal('show');
        });

        // Inisialisasi Select2 untuk Edit Modal (saat modal ditampilkan)
        $('#editReportModal').on('shown.bs.modal', function() {
            // Hanya inisialisasi jika belum diinisialisasi sebelumnya di modal ini
            // Cek apakah select2 sudah diinisialisasi
            if (!$('#edit_Id_Member').hasClass('select2-hidden-accessible')) {
                $('#edit_Id_Member').select2({
                    dropdownParent: $('#editReportModal'),
                    placeholder: "Select Member",
                    allowClear: true
                });
            }
            // Tidak perlu tambahkan class form-control ke Select2 selection di sini kecuali diperlukan styling tambahan
        });

        // Reset Select2 saat modal ditutup (opsional, bisa membantu jika ingin selalu inisialisasi ulang)
        $('#editReportModal').on('hidden.bs.modal', function() {
            // Misalnya, reset pilihan dan hapus error state jika ada
            // $('#edit_Id_Member').val(null).trigger('change.select2'); // Reset pilihan
            // $('#editReportForm')[0].reset(); // Ini akan mereset semua input, termasuk date, ke nilai defaultnya (kosong atau nilai awal HTML)
            // Jika ingin mereset form tanpa mengganggu inisialisasi Select2 berikutnya, hindari reset() atau lakukan reset secara manual hanya pada input tertentu.
        });

        // Script untuk Add Modal (jika belum ada)
        $('#addReportModal').on('shown.bs.modal', function() {
            // Hanya inisialisasi jika belum diinisialisasi sebelumnya di modal ini
            if (!$('select[name="Id_Member[]"]').hasClass('select2-hidden-accessible')) {
                $('select[name="Id_Member[]"]').select2({
                    dropdownParent: $('#addReportModal'),
                    placeholder: "Select Members",
                    allowClear: true
                });
            }
            // Tidak perlu tambahkan class form-control ke Select2 selection di sini kecuali diperlukan styling tambahan
        });
    </script>

    <script>
        // // Event listener untuk Team
        // document.getElementById('team-select').addEventListener('change', function () {
        //     const teamId = this.value; // Id_Team tetap untuk form
        //     const teamName = this.options[this.selectedIndex].text; // Ambil Name_Team untuk fetch
        //     const procedureSelect = document.getElementById('procedure-select');

        //     // Kosongkan dulu option
        //     procedureSelect.innerHTML = `<option value="">Loading...</option>`;

        //     if (!teamName || !teamId) {
        //         procedureSelect.innerHTML = `<option value="">Select Procedure</option>`;
        //         $('#procedure-select').val(null).trigger('change');
        //         return;
        //     }

        //     // Ambil data prosedur berdasarkan team
        //     fetch(`/get-procedures/${encodeURIComponent(teamName)}`)
        //         .then(response => response.json())
        //         .then(data => {
        //             procedureSelect.innerHTML = `<option value="">Select Procedure</option>`;
        //             data.forEach(proc => {
        //                 procedureSelect.innerHTML += `<option value="${proc.Name_Procedure}">${proc.Name_Procedure}</option>`;
        //             });

        //             // Re-inisialisasi Select2 setelah update
        //             $('#procedure-select').select2({
        //                 dropdownParent: $('#addReportModal'),
        //                 placeholder: "Select Procedure",
        //                 allowClear: true,
        //                 width: '100%'
        //             });

        //             $('#procedure-select').next('.select2-container').find('.select2-selection').addClass('form-control');
        //         })
        //         .catch(err => {
        //             console.error(err);
        //             procedureSelect.innerHTML = `<option value="">Error loading procedures</option>`;
        //         });
        // });
    </script>
@endsection

