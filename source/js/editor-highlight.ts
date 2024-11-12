class EditorHighlight {
  private timedOut: boolean = false;
  private img: HTMLImageElement = new Image();
  private timer: number | null = null;

  constructor(
      private config: brokenLinkEditorHighlightData
  ) {
      this.initializeHighlight();
  }

  // Initialize the client type check
  private initializeHighlight(): void {
    console.log('initializeHighlight');
  }
}

// Interfaces
interface brokenLinkEditorHighlightData {
  links: string[];
}

declare global {
  interface Window {
    brokenLinkEditorHighlightData?: brokenLinkEditorHighlightData;
  }
}

// @ts-ignore Function to initialize client type checker  
export function initializeEditorHighlight(brokenLinkEditorHighlightData): void {
  document.addEventListener("DOMContentLoaded", () => {
      new EditorHighlight(brokenLinkEditorHighlightData);
  });
}
// @ts-ignore
initializeEditorHighlight(brokenLinkEditorHighlightData);