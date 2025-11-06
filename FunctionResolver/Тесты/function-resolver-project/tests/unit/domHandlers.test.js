const { setupFormHandler } = require("../../src/js/domHandlers");

describe("DOM Handlers", () => {
  let mockForm;

  beforeEach(() => {
    mockForm = {
      addEventListener: jest.fn(),
    };

    global.document.getElementById = jest.fn((id) => {
      if (id === "calcForm") return mockForm;
      return null;
    });

    global.document.addEventListener = jest.fn();
  });

  test("should setup form handler", () => {
    setupFormHandler();

    // Проверяем, что обработчик добавлен к форме
    expect(mockForm.addEventListener).toHaveBeenCalledWith(
      "submit",
      expect.any(Function)
    );
  });

  test("should not setup handler if form not found", () => {
    document.getElementById.mockReturnValue(null);

    expect(() => setupFormHandler()).not.toThrow();
  });
});
