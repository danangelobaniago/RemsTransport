<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#111827">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Edit Driver | Admin Panel</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.svg">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --bg: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --white: #ffffff;
            --border: #e2e8f0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 550px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 500;
            font-size: 14px;
            margin-bottom: 20px;
            transition: color 0.2s;
        }

        .back-link:hover { color: var(--primary); }

        .card {
            background: var(--white);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border);
        }

        .card-header {
            margin-bottom: 30px;
            text-align: center;
        }

        .card-header h2 {
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
        }

        .card-header p {
            color: var(--text-muted);
            font-size: 14px;
            margin-top: 5px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 15px;
            color: var(--text-main);
            transition: all 0.2s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        /* NEW: License Management Styling */
        .license-management {
            background: #f1f5f9;
            padding: 20px;
            border-radius: 12px;
            margin-top: 10px;
            border: 1px solid var(--border);
        }

        .current-license-preview {
            width: 100%;
            height: 150px;
            border-radius: 8px;
            object-fit: cover;
            margin-bottom: 15px;
            border: 2px solid var(--white);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }

        .btn-submit {
            width: 100%;
            background-color: var(--primary);
            color: var(--white);
            padding: 14px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .btn-submit:hover { background-color: var(--primary-dark); }
        .btn-submit:active { transform: scale(0.98); }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .status-available { background: #dcfce7; color: #166534; }
        .status-unavailable { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>

<div class="container">
    <a href="/admin/drivers" class="back-link">
        <i class="fas fa-arrow-left"></i> &nbsp; Back to Drivers List
    </a>

    <div class="card">
        <div class="card-header">
            <div class="status-badge {{ $driver->status === 'available' ? 'status-available' : 'status-unavailable' }}">
                {{ strtoupper($driver->status) }}
            </div>
            <h2>Edit Driver</h2>
            <p>Modify profile and license documentation.</p>
        </div>

        <form action="{{ url('/admin/drivers/update/' . $driver->id) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" value="{{ old('name', $driver->name) }}" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone', $driver->phone) }}">
            </div>

            <div class="form-group">
                <label for="license">License Number</label>
                <input type="text" id="license" name="license" value="{{ old('license', $driver->license_number) }}">
            </div>

            <div class="form-group">
                <label>Driver's License Document</label>
                <div class="license-management">
                    @if($driver->license_image)
                        <img src="{{ \Storage::disk('public')->url($driver->license_image) }}" class="current-license-preview" id="preview">
                    @endif
                    <input type="file" name="license_image" accept="image/*" onchange="previewImage(event)">
                    <p style="font-size: 11px; color: var(--text-muted); margin-top: 8px;">
                        <i class="fas fa-info-circle"></i> Leave empty to keep the current photo.
                    </p>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </form>
    </div>
</div>

<script>
    // Real-time image preview before saving
    function previewImage(event) {
        var reader = new FileReader();
        reader.onload = function(){
            var output = document.getElementById('preview');
            output.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>

</body>
</html>
