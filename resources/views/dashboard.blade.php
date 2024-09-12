<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(to bottom right, #2d3748, #1a202c);
            transition: background-color 0.3s ease;
            color: #cbd5e0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            background: linear-gradient(to bottom right, #2d3748, #1a202c);
        }

        .card {
            background: linear-gradient(to bottom right, #2d3748, #1a202c);
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.3);
        }

        .header {
            background-color: #4a5568;
            color: #cbd5e0;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            padding: 20px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
        }

        .sidebar {
            background-color: #2d3748;
            border-bottom-left-radius: 12px;
            color: #cbd5e0;
            padding: 20px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
        }

        .sidebar a {
            background-color: #2d3748;
            color: #cbd5e0;
            transition: background-color 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
        }

        .sidebar a:hover {
            background-color: #4a5568;
        }

        .card-item {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .sidebar button[type="submit"] {
            background-color: transparent;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            transition: background-color 0.3s ease;
        }

        .sidebar button[type="submit"]:hover {
            background-color: #4a5568;
            color: #cbd5e0;
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
            }

            100% {
                opacity: 1;
            }
        }

        @keyframes slideIn {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(0);
            }
        }
    </style>
</head>

<body>
    <div class="container py-6">
        <div class="card">
            <div class="header py-4 px-6 flex justify-between items-center">
                <h4 class="font-bold text-lg">Dashboard</h4>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn py-2 px-4 rounded">Logout</button>
                </form>
            </div>
            @php
                $user = Auth::user();
            @endphp
            <div class="flex">
                <div class="sidebar w-1/4 p-4">
                    <ul>
                        <li class="py-2"><a href="#" class="text-gray-300 hover:text-white">Home</a></li>
                        <li class="py-2"><a href="#" class="text-gray-300 hover:text-white">Profile</a></li>
                        <li class="py-2"><a href="#" class="text-gray-300 hover:text-white">Settings</a></li>
                        <li class="py-2"><a href="#" class="text-gray-300 hover:text-white">Messages</a></li>
                        <li class="py-2">
                            <form id="regenerateRecoveryCodesForm" method="POST"
                                action="{{ route('regenerate-recovery-codes') }}">
                                @csrf
                                <button id="regenerateRecoveryCodes" type="submit"
                                    class="text-gray-300 hover:text-white">
                                    {{ __('messages.regenerate_recovery_codes') }}
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
                <div class="content w-3/4">
                    <div class="p-6">
                        <h2 class="text-2xl font-bold text-gray-300">Welcome, {{ $user->name }}</h2>
                        <p class="text-gray-400 mb-4">Here's your performance overview.</p>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div class="bg-gray-800 rounded-lg shadow-lg p-4 card-item">
                                <h3 class="text-lg font-bold text-gray-300 mb-2">Monthly Revenue</h3>
                                <canvas id="revenueChart"></canvas>
                            </div>
                            <div class="bg-gray-800 rounded-lg shadow-lg p-4 card-item">
                                <h3 class="text-lg font-bold text-gray-300 mb-2">User Growth</h3>
                                <canvas id="userGrowthChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Password Modal -->
    <div id="passwordModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="modal-content inline-block align-bottom bg-gray-700 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="modal-header px-6 py-4 bg-gray-700">
                    <h2 class="text-lg font-bold text-gray-300" id="modal-title">Enter Password</h2>
                </div>
                <form id="passwordForm" method="POST" action="{{ route('verify-password') }}">
                    @csrf
                    <div class="modal-body px-6 py-4">
                        <input type="password" name="password"
                            class="mt-1 p-2 w-full border rounded bg-gray-700 text-gray-300" required>
                        <div id="passwordWarning" class="text-red-500 mt-2 hidden">Invalid password. Please try again.</div>
                    </div>
                    <div class="modal-footer px-6 py-4 bg-gray-700">
                        <button type="submit"
                            class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-gray-800 text-base font-medium text-gray-300 hover:bg-gray-900">Submit
                        </button>
                        <button type="button" id="cancelBtn"
                            class="mt-3 inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-gray-700 text-base font-medium text-gray-300 hover:bg-gray-800">Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('regenerateRecoveryCodes').addEventListener('click', function(event) {
            event.preventDefault();
            document.getElementById('passwordModal').classList.remove('hidden');
        });

        document.getElementById('cancelBtn').addEventListener('click', function() {
            document.getElementById('passwordModal').classList.add('hidden');
            document.getElementById('passwordWarning').classList.add('hidden');
        });

        document.getElementById('passwordForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const password = document.querySelector('input[name="password"]').value;

            fetch('{{ route('verify-password') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        password: password
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // If password is correct, submit the regenerate form
                        document.getElementById('regenerateRecoveryCodesForm').submit();
                    } else {
                        document.getElementById('passwordWarning').classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        });
    </script>
    <script>
        // Monthly Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueGradient = revenueCtx.createLinearGradient(0, 0, 0, 400);
        revenueGradient.addColorStop(0, 'rgba(54, 185, 204, 0.8)');
        revenueGradient.addColorStop(1, 'rgba(54, 185, 204, 0.2)');

        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Revenue',
                    data: [12, 19, 3, 5, 2, 3],
                    borderColor: 'rgba(54, 185, 204, 1)',
                    backgroundColor: revenueGradient,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: 'rgba(54, 185, 204, 1)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(54, 185, 204, 1)',
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)',
                            borderDash: [5, 5]
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.7)',
                            font: {
                                size: 12,
                                weight: 'bold'
                            }
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)',
                            borderDash: [5, 5]
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.7)',
                            font: {
                                size: 12,
                                weight: 'bold'
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: 'rgba(255, 255, 255, 0.9)',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        }
                    }
                },
                elements: {
                    line: {
                        borderWidth: 3
                    }
                }
            }
        });

        // User Growth Chart
        const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
        new Chart(userGrowthCtx, {
            type: 'bar',
            data: {
                labels: ['Q1', 'Q2', 'Q3', 'Q4'],
                datasets: [{
                    label: 'New Users',
                    data: [5000, 7000, 6000, 8000],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(255, 206, 86, 0.6)',
                        'rgba(75, 192, 192, 0.6)',
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.7)'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.7)'
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>
