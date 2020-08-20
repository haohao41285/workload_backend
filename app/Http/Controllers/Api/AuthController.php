<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterFormRequest;
use App\Models\Permission;
use App\Models\RolePermission;
use App\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller {
	public function register(RegisterFormRequest $request) {
		$params = $request->only('email', 'name', 'password');
		$user = new User();
		$user->email = $params['email'];
		$user->name = $params['name'];
		$user->password = bcrypt($params['password']);
		$user->save();

		return response()->json($user, Response::HTTP_OK);
	}

	public function login(Request $request) {
		DB::beginTransaction();
		try {
			$credentials = $request->only('email', 'password');
			if (!($token = JWTAuth::attempt($credentials))) {
				return response()->json([
					'status' => 'error',
					'msg' => 'Information Login Incorect!',
				]);
			}

			$data = User::where("email", $request->email)->with('team')->first();
			if ($data->active != 1) {
				return response()->json(['status' => 'error', 'message' => 'Can not login now']);
			}
			//Insert or update _token_api
			User::where('email', $request->email)->update(['_token_api' => $token]);

			//Get permissions
			$per_arr = [];
			if ($data->id_role != "" && $data->id_role != null) {
				$permissions = RolePermission::where('id_role', $data->id_role)->first();
				if ($permissions->permissions != "" && $permissions->permissions != null) {
					$permission_arr = explode(";", $permissions->permissions);
					$permissions = Permission::whereIn('id', $permission_arr)->select('id', 'name', 'slug')->get()->toArray();
					foreach ($permissions as $key => $p) {
						$per_arr[] = "/" . $p['slug'];
					}
				} else {
					$permissions = [];
				}
			} else {
				$permissions = [];
			}

			DB::commit();

			return response()->json(['token' => $token, 'data' => $data, 'permissions' => $permissions, 'per_arr' => $per_arr], Response::HTTP_OK);
		} catch (\Exception $e) {
			\Log::info($e);
			DB::rollBack();
			return response()->json([
				'status' => 'error',
				'msg' => 'Information Login Incorect!',
			]);
		}

	}

	public function user(Request $request) {
		$user = Auth::user();

		if ($user) {
			return response($user, Response::HTTP_OK);
		}

		return response(null, Response::HTTP_BAD_REQUEST);
	}

	/**
	 * Log out
	 * Invalidate the token, so user cannot use it anymore
	 * They have to relogin to get a new token
	 *
	 * @param Request $request
	 */
	public function logout(Request $request) {
		$this->validate($request, ['token' => 'required']);

		try {
			JWTAuth::invalidate($request->input('token'));
			return response()->json('You have successfully logged out.', Response::HTTP_OK);
		} catch (JWTException $e) {
			return response()->json('Failed to logout, please try again.', Response::HTTP_BAD_REQUEST);
		}
	}

	public function refresh() {
		return response(JWTAuth::getToken(), Response::HTTP_OK);
	}
}
