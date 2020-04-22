namespace Domain.Protocol
{
    public class DataExportResquest
    {
        public bool Encrypt { get; set; } = true;
        public string Password { get; set; }
    }
}