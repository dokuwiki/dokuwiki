/**
 * Tests for the LinkWizard class
 *
 * This is a simple self-contained test suite until we introduce a proper way to run JavaScript tests
 * in DokuWiki.
 *
 * Needs to be run manually as:
 *
 * cat linkwiz.js linkwiz.test.js | node
 */

function runLinkWizardTests() {
    const testCases = [
        { ref: 'a:b:c', id: 'a:b:d', expected: 'd' },
        { ref: 'a:b:c', id: 'a:b:c:d:e', expected: '.:c:d:e' },
        { ref: 'a:b:c', id: 'a:b:c:d:e', expected: '.:c:d:e' },
        { ref: 'a', id: 'a:b:c', expected: 'a:b:c' },
        { ref: 'a:b', id: 'c:d', expected: 'c:d' },
        { ref: 'a:b:c', id: 'a:d:e', expected: '..:d:e' },
        { ref: 'a:b:c:d', id: 'a:d:e', expected: '..:..:d:e' },
        { ref: 'a:b', id: 'c', expected: ':c' },
    ];

    testCases.forEach(({ ref, id, expected }, index) => {
        const result = LinkWizard.createRelativeID(ref, id);
        if (result === expected) {
            console.log(`Test ${index + 1} passed`);
        } else {
            console.log(`Test ${index + 1} failed: expected ${expected}, got ${result}`);
        }
    });
}

// Run the tests
runLinkWizardTests();
