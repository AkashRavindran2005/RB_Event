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
        
        
        /* New Classes for coloring */
        .text-red { color: #ff5f56; }
        .text-yellow { color: #ffbd2e; }
        .text-green { color: #27c93f; }
        .text-blue { color: #5aa9e6; }
        
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

        // Helper: Color Text
        const color = (text, cls) => `<span class="text-${cls}">${text}</span>`;
        
        // Helper: Delay
        const delay = (ms) => new Promise(res => setTimeout(res, ms));

        // Command Logic
        const COMMANDS = {
            'help': async () => {
                return `
<span class="text-yellow">AVAILABLE COMMANDS:</span>
  <span class="text-green">scan</span> &lt;target&gt;      - Run comprehensive port scan (Nmap)
  <span class="text-green">analyze</span> &lt;target&gt;   - Generate Threat Intelligence Report
  <span class="text-green">whois</span> &lt;target&gt;     - Query ASN and Registrar info
  <span class="text-green">clear</span>               - Clear terminal screen
  <span class="text-green">history</span>             - Show command history
  <span class="text-green">exit</span>                - Close session
`;
            },
            'scan': async (args) => {
                if (!args) return '<span class="text-red">Error: Target IP required. Usage: scan &lt;ip&gt;</span>';
                
                addLine(`Starting Nmap 7.94 ( https://nmap.org ) at ${new Date().toTimeString().split(' ')[0]}`);
                await delay(500);
                addLine(`Initiating SYN Stealth Scan against ${args}...`);
                await delay(1000);
                addLine(`Scanning ${args} [1000 ports]`);
                await delay(1200);
                addLine(`Discovered open port 80/tcp on ${args}`);
                await delay(300);
                addLine(`Discovered open port 22/tcp on ${args}`);
                await delay(300);
                addLine(`Discovered open port 443/tcp on ${args}`);
                
                return `
Completed SYN Stealth Scan against ${args} in 3.14s
<span class="text-green">PORT    STATE SERVICE     VERSION</span>
22/tcp  open  ssh         OpenSSH 8.2p1 Ubuntu 4ubuntu0.5
80/tcp  open  http        Apache httpd 2.4.41 ((Ubuntu))
443/tcp open  ssl/http    Apache httpd 2.4.41
3306/tcp open mysql       MySQL 8.0.28-0ubuntu0.20.04.3

<span class="text-yellow">OS details:</span> Linux 4.15 - 5.6, Linux 5.0 - 5.4
<span class="text-yellow">Aggressive OS guesses:</span> Linux 5.0 (96%), Linux 5.4 (96%)
<span class="text-yellow">Network Distance:</span> 12 hops
<span class="text-red">TRACEROUTE (using port 443/tcp)</span>
HOP RTT      ADDRESS
1   0.45 ms  192.168.1.1
... 
12  12.00 ms ${args}

Nmap done: 1 IP address (1 host up) scanned in 3.45 seconds
`;
            },
            'analyze': async (args) => {
                 if (!args) return '<span class="text-red">Error: Target IP required. Usage: analyze &lt;ip&gt;</span>';
                 
                 addLine(`[+] Querying local threat database for ${args}...`);
                 await delay(800);
                 addLine(`[+] correlate_logs.py --target ${args}`);
                 await delay(800);
                 addLine(`[+] Analyzing behavioral patterns...`);
                 await delay(1000);
                 
                 const riskScore = Math.floor(Math.random() * (100 - 60 + 1) + 60); // Random 60-100
                 
                 return `
<span class="text-yellow">--- THREAT INTELLIGENCE REPORT ---</span>
<span class="text-green">Target:</span>       ${args}
<span class="text-green">Risk Score:</span>   <span class="text-red">${riskScore}/100 (CRITICAL)</span>
<span class="text-green">Confidence:</span>   High (92%)
<span class="text-green">ISP:</span>          DigitalOcean, LLC
<span class="text-green">Location:</span>     Frankfurt, Germany (DE)
<span class="text-green">ASN:</span>          AS14061

<span class="text-yellow">--- DETECTED ANOMALIES ---</span>
[!] <span class="text-red">Matches known botnet signature (Mirai Variant)</span>
[!] High frequency scanning detected (Port 22, 23, 80)
[!] Multiple failed authentication attempts (SSH Root)
[!] Correlated with CVE-2023-XXXX exploit attempts

<span class="text-yellow">--- RECOMMENDED ACTIONS ---</span>
1. <span class="text-green">Block subnet 192.168.0.0/24 immediately.</span>
2. Reset credentials for compromised accounts.
3. Patch OpenSSH services.
`;
            },
            'whois': async (args) => {
                if (!args) return '<span class="text-red">Error: Target IP required. Usage: whois &lt;ip&gt;</span>';
                await delay(500);
                 return `
% This is the RIPE Database query service.
% The objects are in RPSL format.

inetnum:        ${args} - ${args}
netname:        SUSPICIOUS-NET-BLK-01
descr:          Simulated Malicious Actor Network
country:        DE
admin-c:        ACT1-RIPE
tech-c:         ACT1-RIPE
status:         ASSIGNED PA
mnt-by:         RIPE-NCC-HM-MNT
created:        2024-01-01T12:00:00Z
last-modified:  2024-02-01T12:00:00Z
source:         RIPE

person:         Bad Actor
address:        1234 Dark Web Ave, Frankfurt
phone:          +00 000 000 000
nic-hdl:        ACT1-RIPE
mnt-by:         MAINT-Simulated
created:        2024-01-01T12:00:00Z
last-modified:  2024-02-01T12:00:00Z
source:         RIPE
                 `;
            },
             'clear': async () => {
                history.innerHTML = '';
                return ''; // No output, just clears
            },
            'history': async () => {
                 // Mock history
                 return `
   1  scan 192.168.1.5
   2  analyze 192.168.1.5
   3  whois 192.168.1.5
   4  exit
                 `;
            },
             'exit': async () => {
                 window.close();
                 return 'Session terminated.';
            }
        };

        // UI Logic
        async function processCommand(cmd) {
            const parts = cmd.split(' ');
            const baseCmd = parts[0].toLowerCase();
            const args = parts.slice(1).join(' ');

            if (COMMANDS[baseCmd]) {
                const output = await COMMANDS[baseCmd](args);
                if (output) addLine(output);
            } else {
                addLine(`<span class="text-red">Command not found: ${baseCmd}. Type 'help' for available commands.</span>`);
            }
        }

        function addLine(text, isCommand = false) {
            const div = document.createElement('div');
            div.className = 'output-line';
            if (isCommand) {
                div.innerHTML = `<span class="prompt">analyst@soc:~$</span> <span class="command-entered">${text}</span>`;
            } else {
                div.innerHTML = text;
            }
            history.appendChild(div);
            // Smooth scroll to bottom
            setTimeout(() => {
                body.scrollTop = body.scrollHeight;
            }, 10);
        }

        input.addEventListener('keydown', async (e) => {
            if (e.key === 'Enter') {
                const cmd = input.value.trim();
                input.value = '';

                if (cmd) {
                    addLine(cmd, true);
                    await processCommand(cmd);
                }
            }
        });

        // Check for target param
        const urlParams = new URLSearchParams(window.location.search);
        const target = urlParams.get('target');
        
        if (target) {
            // Clear previous history for a clean start on window reuse
            history.innerHTML = '';
            
            setTimeout(() => {
                const cmd = `analyze ${target}`;
                // Simulate typing: Add to history as if user typed it
                addLine(cmd, true); 
                // Clear input (so it doesn't double run if user hits Enter)
                input.value = '';
                // Run command
                processCommand(cmd);
            }, 300);
        }

        // Click focus fallback
        document.addEventListener('click', () => {
            input.focus();
        });
    </script>
</body>
</html>
