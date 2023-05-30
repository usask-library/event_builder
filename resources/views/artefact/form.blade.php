@include('status')

<div class="form-row">
    <div class="form-group col-md-3">
        <label for="id">Object ID</label>
        <input id="id" name="id" type="text" class="form-control @error('id') is-invalid @enderror" placeholder="Enter the Object ID" value="{{old('id', $object->id)}}">
        @error('id')
        <div class="form-text text-danger is-invalid">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-md-9">
        <label for="name">Name</label>
        <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" placeholder="Enter the object name" value="{{old('name', $object->name)}}">
        @error('name')
        <div class="alert alert-danger">{{ $message }}</div>
        @enderror
    </div>
</div>
