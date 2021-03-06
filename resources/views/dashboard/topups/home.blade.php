@extends('dashboard.layout.app')

@section('body-class','2-column')
@section('page-name','Topups')

@push('css')
    <link rel="stylesheet" type="text/css" href="/css/pages/datatables.min.css">
@endpush

@section('content')
    <section id="basic-datatable">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header justify-content-center align-items-center">
                        <h4 class="card-title"><i class="feather icon-codepen"></i> Topup History</h4>
                    </div>
                    <div class="card-content">
                        <div class="card-body card-dashboard">
                            <div class="table-responsive">
                                <table class="table zero-configuration">
                                    <thead>
                                    <tr>
                                        <th>Timestamp</th>
                                        <th>Operator</th>
                                        <th>Number</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($topups as $topup)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($topup['created_at'])->format('Y-m-d H:m') }}</td>
                                        <td>{{ $topup['operator']['name'] }}</td>
                                        <td>{{ $topup['number'] }}</td>
                                        <td>{{ number_format($topup['topup'],2).' '.$topup['receiver_currency'] }}</td>
                                        <td>
                                            @switch($topup['status'])
                                                @case('PENDING')
                                                <div class="badge badge-pill badge-primary">Pending</div>
                                                @break
                                                @case('SUCCESS')
                                                    @if(isset($topup['pin']) && sizeof($topup['pin']) > 0)
                                                        <button class="btn btn-sm round btn-success" data-toggle="modal-feed" data-target="#modal_lg" data-feed="history/{{ $topup['id'] }}/pin_detail">Pin Available</button>
                                                    @else
                                                        <div class="badge badge-pill badge-success">Success</div>
                                                    @endif
                                                @break
                                                @case('FAIL')
                                                <button class="btn btn-sm round btn-danger" data-toggle="modal-feed" data-target="#modal_lg" data-feed="history/{{ $topup['id'] }}/failed">Failed</button>
                                                @if(Auth::user()['user_role']['name'] == 'ADMIN')
                                                    <button class="btn btn-sm round btn-warning" data-toggle="retry-feed" data-target="#modal_lg" data-feed="{{ $topup['id'] }}/retry">Retry</button>
                                                @endif
                                                @break
                                                @case('PENDING_PAYMENT')
                                                <div class="badge badge-pill badge-secondary">Pending Payment</div>
                                                @break
                                            @endswitch
                                        </td>
                                    </tr>
                                    @endforeach
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <th>Timestamp</th>
                                        <th>Operator</th>
                                        <th>Number</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @include('dashboard.layout.modals')
@endsection

@push('js')
    <script src="/js/datatable/pdfmake.min.js"></script>
    <script src="/js/datatable/vfs_fonts.js"></script>
    <script src="/js/datatable/datatables.min.js"></script>
    <script src="/js/datatable/datatables.buttons.min.js"></script>
    <script src="/js/datatable/buttons.html5.min.js"></script>
    <script src="/js/datatable/buttons.print.min.js"></script>
    <script src="/js/datatable/buttons.bootstrap.min.js"></script>
    <script src="/js/datatable/datatables.bootstrap4.min.js"></script>
    <script>
        $('.zero-configuration').DataTable();
    </script>
    <script>
        $(document).on('click', '[data-toggle="retry-feed"]', function (e) {
            e.preventDefault();
            let url = $(this).attr('data-feed');
            let title = $(this).attr('data-title');
            let text = $(this).attr('data-text');
            swal.fire({
                title: title?title:'Are you sure?',
                html: text?text:'Do you want to retry sending topup?',
                type: 'warning',
                showCancelButton: !0,
                confirmButtonText: 'Yes, Do it!',
                cancelButtonText: 'No, cancel!',
                reverseButtons: !0

            }).then(function (e) {
                if (e.value) {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {},
                        error: function (response) {
                            response = $.parseJSON(response.responseText);
                            $.each(response, function (key, value) {
                                if ($.isPlainObject(value)) {
                                    $.each(value, function (key, value) {
                                        toastr.error(value, 'Error');
                                    });
                                }
                            });
                        },
                        success: function (response) {
                            if (response.message) {
                                toastr.success(response.message, 'Success');
                            } else {
                                toastr.success('All Done', 'Success');
                            }
                            if (response.location)
                                window.location = response.location;
                        }
                    });
                }
            });
        });
    </script>

@endpush
