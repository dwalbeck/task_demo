angular.module('myApp', ['ngRoute'])
	.factory("APIService", function($q, $timeout, $http) {
		'use strict';

		var APIService = {};

		APIService.getTasks = function () {
			var req = {
				method: 'GET',
				url: "/api_service/tasks",
				headers: { 'content-type': 'application/json' }
			};
			return $http(req);
		};

		APIService.getTask = function(taskID) {
			var req = {
				method: 'GET',
				url: "/api_service/task?task_id=" + taskID,
				headers: { 'content-type': 'application/json' }
			};
			return $http(req);
		};

		APIService.insertTask = function(task) {
			var req = {
				method: 'POST',
				url: "/api_service/postTask",
				headers: { 'content-type': 'application/json' },
				data: task
			};
			return $http(req);
		};

		APIService.updateTask = function(taskid, task) {
			console.log('update data', task);
			var req = {
				method: 'PUT',
				url: "/api_service/putTask?task_id="+taskid,
				headers: { 'content-type': 'application/json' },
				data: task
			};
			return $http(req);
		};

		APIService.deleteTask = function(taskid) {
			var req = {
				method: 'DELETE',
				url: "/api_service/deleteTask?task_id=" + taskid,
				headers: { 'content-type': 'application/json' }
			};
			return $http(req);
		};

		APIService.toggleTask = function(taskid, status) {
			var req = {
				method: 'PUT',
				url: "/api_service/upTask?task_id=" + taskid + "&status=" + status,
				headers: { 'content-type': 'application/json' }
			};
			return $http(req);
		};

		return APIService;
	})
	.controller('TaskController', function ($scope, $rootScope, $location, $routeParams, APIService) {

		APIService.getTasks().then(function(result) {
			if (result.status === 200) {
				console.log('TASKS', result);
				$scope.tasks = result.data;
			} else {
				alert('An error occurred while retrieving tasks');
			}
		});

		$scope.deleteTask = function (task_id) {
			$location.path('/');
			if (confirm("Are you sure to delete task: " + task_id) === true)
				APIService.deleteTask(task_id);
		};

		$scope.toggleStatus = function (taskid, completed) {
			APIService.toggleTask(taskid, completed).then(function(result) {
				// show display of outcome
				if (result.status !== 200) {
					alert('Failed to update status of task');
				} else {
					_.each($scope.tasks, function(row, index) {
						if (row.task_id === taskid) { // found entry

							var tmp = (completed !== null ? null : moment().format('YYYY-MM-DD HH:mm:ss'));
							$scope.tasks[index].task_completed = tmp;
						}
					});
				}
			});
		};
	})
	.controller('EditController', function ($scope, $rootScope, $location, APIService, $routeParams) {

		var taskID = ($routeParams.taskID) ? parseInt($routeParams.taskID) : 0;
		$rootScope.title = (taskID > 0) ? 'Edit Task' : 'Add Task';
		$scope.buttonText = (taskID > 0) ? 'Update Task' : 'Add New Task';

		if (taskID > 0) {
			APIService.getTask(taskID).then(function(result) {
				if (result.status === 200) {
					$scope.task = result.data[0];
				} else {
					alert('Failed to retrieve task information');
				}
			});
		} else {
			$scope.task = {};
		}

		var original = $scope.task;
		$scope.task_id = taskID;


		$scope.isClean = function () {
			return $scope.task;
			//return angular.equals(original, $scope.task);
		};

		$scope.saveTask = function (task) {
			console.log('TASK VALUES', task);
			//$location.path('/');
			if (taskID <= 0) {
				APIService.insertTask(task);
			} else {
				APIService.updateTask(taskID, task);
			}
		};
	})
	.config(['$routeProvider',
		function ($routeProvider) {
			$routeProvider.when('/', {
				title: 'Tasks',
				templateUrl: 'app/partials/task_list.html',
				controller: 'TaskController'
			})
			.when('/edit-task/:taskID', {
				title: 'Edit Task',
				templateUrl: 'app/partials/edit_task.html',
				controller: 'EditController',
			})
			.otherwise({
				redirectTo: '/'
			});
		}
	])
	.run(['$location', '$rootScope', function ($location, $rootScope) {
		$rootScope.$on('$routeChangeSuccess', function (event, current, previous) {
			$rootScope.title = current.$$route.title;
		});
	}]);
