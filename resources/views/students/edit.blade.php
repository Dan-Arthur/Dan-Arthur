@extends('layouts.app')

@section('title', 'Edit Student')

@section('breadcrumbs')
<a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
<span class="text-gray-400 mx-1">/</span>
<a href="{{ route('students.index') }}" class="text-gray-500 hover:text-gray-700">Students</a>
<span class="text-gray-400 mx-1">/</span>
<a href="{{ route('students.show', $student) }}" class="text-gray-500 hover:text-gray-700">{{ $student->full_name }}</a>
<span class="text-gray-400 mx-1">/</span>
<span class="text-gray-900 font-medium">Edit</span>
@endsection

@section('content')
<form method="POST" action="{{ route('students.update', $student) }}" enctype="multipart/form-data" class="space-y-6">
@csrf
@method('PUT')

<div class="page-header">
    <div>
        <h1 class="page-title">Edit Student</h1>
        <p class="page-subtitle">{{ $student->full_name }} · {{ $student->student_number }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('students.show', $student) }}" class="btn-secondary btn-sm">Cancel</a>
        <button type="submit" class="btn-primary btn-sm">Save Changes</button>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="space-y-4">
        <div class="card card-body text-center" x-data="{ preview: null }">
            <div class="w-24 h-24 rounded-full bg-gray-200 mx-auto overflow-hidden">
                <img :src="preview ?? '{{ $student->photo_url }}'" class="w-full h-full object-cover">
            </div>
            <label class="block mt-3">
                <span class="btn-secondary btn-sm cursor-pointer">Change Photo</span>
                <input type="file" name="photo" accept="image/*" class="hidden"
                    @change="const f = $event.target.files[0]; if(f){ const r = new FileReader(); r.onload = e => preview = e.target.result; r.readAsDataURL(f); }">
            </label>
        </div>

        <div class="card card-body space-y-4">
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-select" required>
                    @foreach(['active','inactive','graduated','transferred','withdrawn','suspended'] as $s)
                    <option value="{{ $s }}" {{ old('status', $student->status) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Current Class</label>
                <select name="current_class_id" class="form-select">
                    <option value="">Unassigned</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ old('current_class_id', $student->current_class_id) == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Admission Date</label>
                <input type="date" name="admission_date" value="{{ old('admission_date', $student->admission_date?->format('Y-m-d')) }}" class="form-input">
            </div>
            <div>
                <label class="form-label">House</label>
                <input type="text" name="house" value="{{ old('house', $student->house) }}" class="form-input">
            </div>
        </div>
    </div>

    <div class="lg:col-span-2 space-y-4">
        <div class="card">
            <div class="card-header"><h3 class="text-sm font-semibold">Personal Information</h3></div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="form-label">First Name *</label>
                        <input type="text" name="first_name" value="{{ old('first_name', $student->first_name) }}" required class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Last Name *</label>
                        <input type="text" name="last_name" value="{{ old('last_name', $student->last_name) }}" required class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Other Names</label>
                        <input type="text" name="other_names" value="{{ old('other_names', $student->other_names) }}" class="form-input">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="form-label">Gender *</label>
                        <select name="gender" class="form-select" required>
                            <option value="male" {{ old('gender', $student->gender) === 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender', $student->gender) === 'female' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $student->date_of_birth?->format('Y-m-d')) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Nationality</label>
                        <input type="text" name="nationality" value="{{ old('nationality', $student->nationality) }}" class="form-input">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="form-label">Religion</label>
                        <select name="religion" class="form-select">
                            <option value="">Select</option>
                            @foreach(['Christianity','Islam','Other'] as $r)
                            <option value="{{ $r }}" {{ old('religion', $student->religion) === $r ? 'selected' : '' }}>{{ $r }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Blood Group</label>
                        <select name="blood_group" class="form-select">
                            <option value="">Unknown</option>
                            @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                            <option value="{{ $bg }}" {{ old('blood_group', $student->blood_group) === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Genotype</label>
                        <select name="genotype" class="form-select">
                            <option value="">Unknown</option>
                            @foreach(['AA','AS','SS','AC','SC'] as $gt)
                            <option value="{{ $gt }}" {{ old('genotype', $student->genotype) === $gt ? 'selected' : '' }}>{{ $gt }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="text-sm font-semibold">Contact Information</h3></div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" value="{{ old('phone', $student->phone) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email', $student->email) }}" class="form-input">
                    </div>
                </div>
                <div>
                    <label class="form-label">Address</label>
                    <textarea name="address" rows="2" class="form-input">{{ old('address', $student->address) }}</textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">City</label>
                        <input type="text" name="city" value="{{ old('city', $student->city) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">State</label>
                        <input type="text" name="state" value="{{ old('state', $student->state) }}" class="form-input">
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('students.show', $student) }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Save Changes</button>
        </div>
    </div>
</div>

</form>
@endsection
