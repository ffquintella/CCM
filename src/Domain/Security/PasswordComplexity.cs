namespace Domain.Security
{
    public class PasswordComplexity
    {
        public int MinSize { get; set; }
        public bool MustContainNumbers { get; set; }
        public bool MustContainSymbols { get; set; }
        public bool MustContainCapLetters { get; set; }
        public bool MustContainLetters { get; set; }
    }
}