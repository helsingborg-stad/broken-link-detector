var brokenLinkContextDetectionData = {
    "domains": ["google.com", "ifg.se", "dev.local.municipio.tech"],
    "tooltip": "Access to this domain is restricted."
};

class ClientTypeChecker {
    private timedOut: boolean = false;
    private img: HTMLImageElement = new Image();
    private timer: number | null = null;
    private isDevToolsOpen: boolean = false;

    constructor(
        private url: string, // URL to the image
        private timeout: number, // Timeout duration in milliseconds
        private internalClass: string, // Class for internal clients
        private externalClass: string, // Class for external clients
        private contextData: { domains: string[]; tooltip: string } // Context data
    ) {
        this.checkDevToolsOpen();
        this.initializeCheck();
    }

    // Initialize the client type check
    private initializeCheck(): void {
        this.setTimer();
        this.loadImage();
    }

    // Set a timeout for image loading
    private setTimer(): void {
        this.timer = window.setTimeout(() => {
            this.timedOut = true;
            this.cancelImageLoad();
            this.setExternalClient('timeout');
        }, this.timeout);
    }

    // Load the image and handle success or failure
    private loadImage(): void {
        this.img.onload = this.handleImageLoad;
        this.img.onerror = this.handleImageError;
        this.img.src = this.url;
    }

    // Handle successful image load (internal client)
    private handleImageLoad = (): void => {
        if (!this.timedOut) {
            this.clearTimer();
            this.setInternalClient();
        }
    }

    // Handle image load error (external client)
    private handleImageError = (): void => {
        if (!this.timedOut) {
            this.clearTimer();
            this.setExternalClient('image error');
        }
    }

    // Clear the timer if image loads or errors before timeout
    private clearTimer(): void {
        if (this.timer) {
            clearTimeout(this.timer);
            this.timer = null;
        }
    }

    // Cancel image loading on timeout
    private cancelImageLoad(): void {
        this.img.src = ''; // Cancel image loading
    }

    // Mark as internal client
    private setInternalClient(): void {
        document.body.classList.add(this.internalClass);
        this.log('Internal client detected (image loaded).');
    }

    // Mark as external client
    private setExternalClient(reason: string): void {
        document.body.classList.add(this.externalClass);
        this.log(`External client detected (${reason}).`);
    }

    // Log messages only if DevTools is open
    private log(message: string): void {
        if (this.isDevToolsOpen) {
            console.log(message);
        }
    }

    // Check if DevTools is open by measuring execution time of the debugger
    private checkDevToolsOpen(): void {
        const start = performance.now();
        debugger;
        const timeTaken = performance.now() - start;
        // DevTools are likely open if execution time is more than 100ms
        this.isDevToolsOpen = timeTaken > 100;
    }

    // Apply domain restrictions to domains in the domain list
    public applyDomainRestrictions(): void {
        this.contextData.domains.forEach(domain => {
            const elements = document.querySelectorAll(`a[href*="${domain}"]`);
            elements.forEach(element => {
                element.setAttribute("disabled", "disabled");
                element.setAttribute("data-tooltip", this.contextData.tooltip);
            });
        });
    }
}

// Function to initialize client type checker
export function initializeClientTypeChecker(
    url: string,
    timeout: number,
    internalClass: string,
    externalClass: string,
    contextData: { domains: string[], tooltip: string }
): void {
    const checker = new ClientTypeChecker(url, timeout, internalClass, externalClass, contextData);
    checker.applyDomainRestrictions();
}

initializeClientTypeChecker(
    'https://example.com/image.jpg',
    3000,
    'internal-client',
    'external-client',
    brokenLinkContextDetectionData
);