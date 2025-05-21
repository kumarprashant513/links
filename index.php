<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Envelope Video Gallery with Plyr</title>

    <!-- Plyr CSS -->
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.2/plyr.css" />

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #fdfdfd;
        }

        .container {
            padding: 3rem 2rem 1rem;
            background: linear-gradient(145deg, #ffffff, #f0f0f0);
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.06);
            margin: 2rem auto;
            max-width: 1200px;
        }

        h1 {
            text-align: center;
            font-size: 2.4rem;
            font-weight: 800;
            background: linear-gradient(90deg, #d16ba5, #86a8e7, #5ffbf1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 2rem;
            text-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .envelope-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
            padding: 2rem 0;
            justify-content: center;
        }

        .envelope-container {
            position: relative;
            width: 320px;
            transition: transform 0.3s ease;
        }

        .envelope-card {
            position: relative;
            width: 100%;
            height: 220px;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
            /* border-radius: 10px; */
            background: #fff;
            cursor: pointer;
        }

        .envelope-flap {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 80px;
            transform-origin: top;
            transition: transform 0.6s ease;
            z-index: 3;
            /* border-top-left-radius: 10px;
            border-top-right-radius: 10px; */
        }

        .envelope-card:hover .envelope-flap {
            transform: rotateX(-140deg);
        }

        .envelope-body {
            position: absolute;
            top: 80px;
            left: 0;
            width: 100%;
            height: calc(100% - 80px);
            text-align: center;
            z-index: 1;
        }

        .plyr__video-embed {
            width: 100%;
            height: 100%;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .envelope-card:hover .plyr__video-embed {
            opacity: 1;
            pointer-events: auto;
        }

        .envelope-title {
            font-weight: 600;
            font-size: 1.1rem;
            margin: 0.8rem 0;
            color: #333;
        }

        .envelope-desc {
            margin-top: 0.6rem;
            font-size: 0.9rem;
            color: #444;
            text-align: center;
            max-width: 320px;
            line-height: 1.3;
            font-style: italic;
            user-select: none;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>Showcase of My Web Development Work</h1>

        <div class="envelope-wrapper">
            <?php
            $templates = json_decode(file_get_contents('templatess.json'), true);

            $bgPairs = [
                ['card' => '#ffe4e1', 'flap' => '#ffd3cc'],
                ['card' => '#e6f7ff', 'flap' => '#cdefff'],
                ['card' => '#f0fff0', 'flap' => '#d4ffe1'],
                ['card' => '#fff9e6', 'flap' => '#ffefc2'],
                ['card' => '#f9f0ff', 'flap' => '#e8d8ff'],
            ];

            foreach ($templates as $index => $t) {
                $pair = $bgPairs[$index % count($bgPairs)];
                $playerId = "player-" . $index;
                preg_match('#(?:youtube\.com/embed/|v=|youtu\.be/)([a-zA-Z0-9_-]{11})#', $t['youtube_url'], $matches);
                $videoId = $matches[1] ?? '';

                echo '
        <div class="envelope-container">
          <div class="envelope-card" style="background:' . $pair['card'] . ';" data-playerid="' . $playerId . '">
            <div class="envelope-flap" style="background:' . $pair['flap'] . ';"></div>
            <div class="envelope-body">
              <div class="plyr__video-embed" id="' . $playerId . '">
                <iframe
                  src="https://www.youtube.com/embed/' . $videoId . '?modestbranding=1&rel=0&showinfo=0&mute=1&enablejsapi=1&controls=0&iv_load_policy=3&fs=0&cc_load_policy=0&autoplay=0"
                  allow="autoplay; fullscreen"
                  allowfullscreen
                  allowtransparency
                  allow="encrypted-media"
                  frameborder="0"
                  ></iframe>
              </div>
            </div>
          </div>
          <div class="envelope-desc">' . htmlspecialchars($t['desc']) . '</div>
        </div>';
            }
            ?>
        </div>
    </div>

    <!-- Plyr JS -->
    <script src="https://cdn.plyr.io/3.7.2/plyr.js"></script>

    <script>
        const players = {};

        document.querySelectorAll('.envelope-card').forEach(card => {
            const playerId = card.getAttribute('data-playerid');
            const container = document.getElementById(playerId);

            let ytPlayer;

            card.addEventListener('mouseenter', () => {
                if (!players[playerId]) {
                    players[playerId] = new Plyr(container, {
                        autoplay: true,
                        muted: true,
                        controls: [],
                        clickToPlay: false,
                        settings: [],
                        keyboard: {
                            focused: false,
                            global: false
                        },
                    });

                    players[playerId].on('ready', event => {
                        ytPlayer = players[playerId].elements?.container?.querySelector('iframe')?.contentWindow;
                    });
                }

                players[playerId].play();
            });

            card.addEventListener('mouseleave', () => {
                if (players[playerId]) {
                    players[playerId].pause();
                    players[playerId].currentTime = 0;
                }
            });

            card.addEventListener('click', () => {
                if (players[playerId]) {
                    players[playerId].fullscreen.enter();

                    setTimeout(() => {
                        if (ytPlayer) {
                            ytPlayer.postMessage(JSON.stringify({
                                event: 'command',
                                func: 'setPlaybackQuality',
                                args: ['hd1080']
                            }), '*');
                        }
                    }, 1000);
                }
            });
        });
    </script>
</body>

</html>