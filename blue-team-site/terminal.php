<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Terminal | Blue Team Training</title>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0d0d0d;
            --terminal-bg: rgba(20, 20, 20, 0.95);
            --text-primary: #00ff41;
            --text-secondary: #008f11;
            --prompt-color: #00ff41;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.8);
            --glow: 0 0 15px rgba(0, 255, 65, 0.2);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-primary);
            font-family: 'Fira Code', monospace;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            background-image: radial-gradient(circle at center, #1a1a1a 0%, #0d0d0d 100%);
        }

        #terminal-container {
            width: 90%;
            max-width: 900px;
            height: 80vh;
            background: var(--terminal-bg);
            border: 1px solid #333;
            border-radius: 8px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(10px);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: relative;
        }

        #terminal-header {
            background: #222;
            padding: 10px 15px;
            display: flex;
            gap: 8px;
            border-bottom: 1px solid #333;
            align-items: center;
        }

        .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }
        .red { background: #ff5f56; }
        .yellow { background: #ffbd2e; }
        .green { background: #27c93f; }

        #terminal-title {
            margin-left: 10px;
            color: #888;
            font-size: 13px;
        }

        #terminal-body {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            scrollbar-width: thin;
            scrollbar-color: #333 transparent;
        }

        #terminal-body::-webkit-scrollbar {
            width: 6px;
        }
        #terminal-body::-webkit-scrollbar-thumb {
            background: #333;
            border-radius: 10px;
        }

        .output-line {
            margin-bottom: 8px;
            white-space: pre-wrap;
            line-height: 1.4;
            animation: fadeIn 0.1s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        #input-container {
            display: flex;
            align-items: center;
            margin-top: 10px;
        }

        .prompt {
            color: var(--prompt-color);
            margin-right: 10px;
            white-space: nowrap;
        }

        #command-input {
            background: transparent;
            border: none;
            color: var(--text-primary);
            font-family: inherit;
            font-size: inherit;
            width: 100%;
            outline: none;
            caret-color: var(--text-primary);
        }

        .command-entered {
            color: #fff;
        }

        .help-table {
            color: var(--text-secondary);
            margin: 10px 0;
        }

        .malicious {
            color: #ff4136;
            text-shadow: 0 0 5px rgba(255, 65, 54, 0.5);
        }
    </style>
</head>
<body>

    <div id="terminal-container">
        <div id="terminal-header">
            <div class="dot red"></div>
            <div class="dot yellow"></div>
            <div class="dot green"></div>
            <div id="terminal-title">analyst@soc-node-01: ~ (zsh)</div>
        </div>
        <div id="terminal-body">
            <div class="output-line">Blue Team Forensic Terminal [Version 2.4.0-STABLE]</div>
            <div class="output-line">Connected to instances: forensics-prod-aws-01</div>
            <div class="output-line">Type 'help' to see available investigation tools.</div>
            <div class="output-line">&nbsp;</div>
            <div id="history"></div>
            <div id="input-container">
                <span class="prompt">analyst@soc:~$</span>
                <input type="text" id="command-input" autofocus autocomplete="off" spellcheck="false">
            </div>
        </div>
    </div>

    <script>
        const body = document.getElementById('terminal-body');
        const history = document.getElementById('history');
        const input = document.getElementById('command-input');

        const COMMANDS = {
            'help': () => `
Investigative Commands:
  last        - Display recent system logins
  netstat -ant - Show active network connections
  ps aux       - List all running processes
  whois <ip>  - Query threat intelligence for IP
  clear       - Clear the terminal screen
  help        - Show this help menu
            `,
            'last': () => `
USER     TTY      FROM              LOGIN TIME   STATUS
test     pts/0    192.168.10.25    Jul 12 16:20  logged in
root     tty2     CONSOLE           Jul 07 08:06  08:09 (00:03)
guest    pts/1    192.168.1.75     Jul 12 10:25  00:02 (15:20)
cyrus    cron     :0                Jul 03 04:07  04:07 (00:00)
news     cron     :0                Jul 03 04:14  04:14 (00:00)
            `.trim(),
            'netstat': (args) => {
                if (args === '-ant') {
                    return `
Active Internet connections (servers and established)
Proto Recv-Q Send-Q Local Address           Foreign Address         State      
tcp        0      0 0.0.0.0:80              0.0.0.0:*               LISTEN     
tcp        0      0 0.0.0.0:22              0.0.0.0:*               LISTEN     
tcp        0      0 0.0.0.0:3306            0.0.0.0:*               LISTEN     
tcp        0      0 10.0.0.5:80             192.168.10.25:54321     ESTABLISHED
tcp        0      0 10.0.0.5:22             192.168.1.50:51223      ESTABLISHED
                    `.trim();
                }
                return 'Usage: netstat -ant';
            },
            'ps': (args) => {
                if (args === 'aux') {
                    return `
USER       PID %CPU %MEM    VSZ   RSS TTY      STAT START   TIME COMMAND
root         1  0.0  0.1  22536  2312 ?        Ss   Jun14   0:02 /sbin/init
root       501  0.0  0.2  45120  4100 ?        S    Jun14   0:05 /usr/sbin/sshd
www-data  1024  0.5  1.2 245360 25412 ?        S    Jul12   0:15 /usr/sbin/apache2 -k start
www-data  1025  0.0  0.8 245360 18210 ?        S    Jul12   0:08 /usr/sbin/apache2 -k start
mysql     2201  0.2  4.5 1254320 95412 ?       Ssl  Jun14   4:22 /usr/sbin/mysqld
                    `.trim();
                }
                return 'Usage: ps aux';
            },
            'clear': () => {
                history.innerHTML = '';
                return null;
            },
            'ping': (args) => {
                if (!args) return 'Usage: ping <host>';
                return `
PING ${args} (${args}) 56(84) bytes of data.
64 bytes from ${args}: icmp_seq=1 ttl=64 time=0.045 ms
64 bytes from ${args}: icmp_seq=2 ttl=64 time=0.052 ms
64 bytes from ${args}: icmp_seq=3 ttl=64 time=0.048 ms
64 bytes from ${args}: icmp_seq=4 ttl=64 time=0.049 ms
--- ${args} ping statistics ---
4 packets transmitted, 4 received, 0% packet loss, time 3000ms
                `.trim();
            },
            'whois': (args) => {
                if (!args) return 'Usage: whois <ip>';
                return `
% This is the RIPE Database query service.
% The objects are in RPSL format.

inetnum:        ${args} - ${args}
netname:        SUSPICIOUS-NET-BLK-01
descr:          Simulated Malicious Actor Network
country:        XX
admin-c:        ACT1-RIPE
tech-c:         ACT1-RIPE
status:         ASSIGNED PA
mnt-by:         RIPE-NCC-HM-MNT
created:        2024-01-01T12:00:00Z
last-modified:  2024-02-01T12:00:00Z
source:         RIPE

person:         Bad Actor
address:        1234 Dark Web Ave
phone:          +00 000 000 000
nic-hdl:        ACT1-RIPE
mnt-by:         MAINT-Simulated
created:        2024-01-01T12:00:00Z
last-modified:  2024-02-01T12:00:00Z
source:         RIPE
                `.trim();
            }
        };

        function addLine(text, isCommand = false) {
            const div = document.createElement('div');
            div.className = 'output-line';
            if (isCommand) {
                div.innerHTML = `<span class="prompt">analyst@soc:~$</span> <span class="command-entered">${text}</span>`;
            } else {
                div.innerHTML = text;
            }
            history.appendChild(div);
            body.scrollTop = body.scrollHeight;
        }

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                const cmd = input.value.trim();
                input.value = '';

                if (cmd) {
                    addLine(cmd, true);
                    
                    const parts = cmd.split(' ');
                    const baseCmd = parts[0];
                    const args = parts.slice(1).join(' ');

                    if (COMMANDS[baseCmd]) {
                        const output = COMMANDS[baseCmd](args);
                        if (output !== null) {
                            setTimeout(() => addLine(output), 50);
                        }
                    } else {
                        setTimeout(() => addLine(`command not found: ${cmd}`), 50);
                    }
                }
            }
        });

        // Check for target param
        const urlParams = new URLSearchParams(window.location.search);
        const target = urlParams.get('target');
        
        if (target) {
            setTimeout(() => {
                // Display target connection message instead of auto-typing command
                addLine(`Target acquired: ${target}`);
                addLine(`Initiating manual investigation protocol...`);
                input.focus();
            }, 500);
        }

        // Click focus fallback
        document.addEventListener('click', () => {
            input.focus();
        });
    </script>
</body>
</html>
