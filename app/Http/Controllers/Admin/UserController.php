<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\Department;
use App\Models\User;
use App\Services\ManageAccounts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->validate(['search' => ['nullable', 'string', 'max:100']])['search'] ?? '';
        $users = User::with('department')->when($search !== '', fn ($query) => $query->where(fn ($query) => $query->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%')->orWhereHas('department', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))))->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users', 'search'));
    }

    public function create()
    {
        return view('admin.users.form', ['user' => new User(['role' => 'employee', 'is_active' => true]), 'departments' => Department::where('is_active', true)->orderBy('name')->get()]);
    }

    public function store(UserRequest $request, ManageAccounts $accounts)
    {
        $accounts->save($request->user(), $request->validated());

        return to_route('admin.users.index')->with('success', 'Account created.');
    }

    public function edit(User $user)
    {
        return view('admin.users.form', ['user' => $user, 'departments' => Department::where(fn ($query) => $query->where('is_active', true)->orWhere('id', $user->department_id))->orderBy('name')->get()]);
    }

    public function update(UserRequest $request, User $user, ManageAccounts $accounts)
    {
        $accounts->save($request->user(), $request->validated(), $user);

        return to_route('admin.users.index')->with('success', 'Account updated.');
    }

    public function password(Request $request, User $user)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
        ]);
        DB::transaction(function () use ($request, $user, $data) {
            $users = User::orderBy('id')->lockForUpdate()->get();
            $actor = $users->firstWhere('id', $request->user()->id);
            abort_unless($actor?->isAdmin() && $actor->is_active, 403);
            if (! \Illuminate\Support\Facades\Hash::check($data['current_password'], $actor->password)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['current_password' => 'Password confirmation failed.']);
            }
            $account = $users->firstWhere('id', $user->id);
            abort_unless($account, 404);
            $account->password = $data['password'];
            $account->setRememberToken(Str::random(60));
            $account->save();
            \Illuminate\Support\Facades\Password::broker()->deleteToken($account);
            if (config('session.driver') === 'database') {
                DB::connection(config('session.connection'))
                    ->table(config('session.table'))->where('user_id', $account->id)->delete();
            }
        }, 3);

        return to_route('admin.users.edit', $user)->with('success', 'Password updated. Existing sessions will require login again.');
    }
}
