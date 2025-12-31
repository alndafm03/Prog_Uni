@extends('admin.layout')

@section('content')

<h2 class="mb-4">إدارة المستخدمين</h2>

<table class="table table-bordered table-striped">
    <thead class="table-dark">
        <tr>
            <th>الاسم</th>
            <th>الهاتف</th>
            <th>الدور</th>
            <th>الحالة</th>
            <th>إجراءات</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($users as $user)
        <tr>
            <td>{{ $user->first_name }} {{ $user->last_name }}</td>
            <td>{{ $user->phone }}</td>
            <td>{{ $user->role }}</td>
            <td>{{ $user->status }}</td>
            <td>
                <button class="btn btn-success btn-sm">قبول</button>
                <button class="btn btn-danger btn-sm">رفض</button>
                <button class="btn btn-warning btn-sm">حذف</button>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@endsection
