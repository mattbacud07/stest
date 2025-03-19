<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service webApp</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            padding: 10px;
            background-color: #ffffff;
            border-radius: 8px;
            /* max-width: 600px; */
            margin: 20px auto;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .content {
            padding: 20px;
            text-align: left;
        }

        .content p {
            font-size: 13px;
            color: #333333;
            margin: 0 0 20px;
            line-height: 20px;
        }

        a {
            color: #ffffff;
        }

        .btn {
            display: inline-block;
            padding: 10px 30px;
            background-color: #191970;
            color: #ffffff;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
        }

        .footer {
            background-color: #f4f4f4;
            padding: 20px;
            text-align: left;
            font-size: 12px;
            color: #777777;
            border-radius: 0 0 8px 8px;
        }

        .footer p {
            margin: 0;
        }
    </style>
</head>

<body>
    
    <div class="container">
        <div class="content">
            <p>A new request is awaiting your approval. Please review the details and take the necessary action at your earliest convenience.</p>
            <p>To review the request, please click the button below:</p><br>
            <a href="{{$link}}" class="btn">Review</a>
        </div>
        <div class="footer">
            <p>For any questions, feel free to contact administrator</p>
            <p>© SBSI. All rights reserved.</p>
        </div>
    </div>

</body>

</html>