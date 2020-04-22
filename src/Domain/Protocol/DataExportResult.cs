namespace Domain.Protocol
{
    public class DataExportResult
    {
        public float FileVersion { get; set; }
        public string FileLink { get; set; }
        
        public DataExportStatus Status { get; set; }
        
        public string ErrorMessage { get; set; }
    }
}