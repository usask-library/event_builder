@include('status')

<div class="form-row">
    <div class="form-group col-md-3">
        <label for="id">ID</label>
        <input id="id" name="id" type="text" class="form-control @error('id') is-invalid @enderror" placeholder="ID" value="{{old('id', $person->id)}}">
        @error('id')
        <div class="form-text text-danger is-invalid">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="first">First name</label>
        <input id="first" name="first" type="text" class="form-control @error('first') is-invalid @enderror" placeholder="First name" value="{{old('first', $person->first)}}">
        @error('first')
        <div class="alert alert-danger">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-md-6">
        <label for="last">Last name</label>
        <input id="last" name="last" type="text" class="form-control @error('last') is-invalid @enderror" placeholder="Last name" value="{{old('last', $person->last)}}">
        @error('last')
        <div class="alert alert-danger">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-12">
        <label for="roles">Roles</label>
        <select id="roles" name="roles[]" class="form-control selectpicker show-tick @error('roles') is-invalid @enderror" multiple>
            @foreach($roles as $role)
                <option value="{{ $role->id }}"
                    @if(old('roles'))
                        {{ (collect(old('roles'))->contains($role->id)) ? 'selected' : '' }}
                    @else
                        {{ ($person->roles->contains($role->id)) ? 'selected' : '' }}
                    @endif
                >{{ $role->name }}</option>
            @endforeach
        </select>
        @error('roles')
        <div class="alert alert-danger">{{ $message }}</div>
        @enderror
    </div>
</div>
