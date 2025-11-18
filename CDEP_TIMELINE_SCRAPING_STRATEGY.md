# CDEP Legislative Timeline Scraping Strategy
**Date**: 2025-11-18
**Status**: DESIGN DOCUMENT

## Overview

The CDEP website displays a complex legislative timeline with:
- Color-coded chambers (Senate, Chamber of Deputies, Presidential)
- Chronological events with dates
- Associated documents (PDFs, DOCs)
- Links to committees, stenograms, video recordings
- Deadlines and special notes
- Visual flow indicators (arrows, colors)

## HTML Structure Analysis

### Bill Metadata Table

```html
<table width="100%" border="0">
  <tr valign="top">
    <td bgcolor="fff0d8">Nr. înregistrare:</td>
  </tr>
  <tr valign="top">
    <td bgcolor="fff0d8">- Camera Deputaţilor:</td>
    <td width="100%"><b>480/17.11.2025</b></td>
  </tr>
  <tr valign="top">
    <td bgcolor="fff0d8">Procedura legislativa:</td>
    <td>cf. Constitutiei revizuita în 2003</td>
  </tr>
  <!-- ... more rows ... -->
</table>
```

**Fields to Extract:**
- Registration numbers (multiple: BPI, Camera Deputatilor, Senat, Guvern)
- Legislative procedure type
- Decision chamber (Camera decizionala)
- Initiative type (Proiect/Propunere legislativă)
- Character (organic/ordinara)
- Urgency status (da/nu)
- Current status with link to final law
- Initiators (Guvern, MPs)
- Documents (Expunere de motive, Forma initiatorului, etc.)

### Timeline Table Structure

```html
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <!-- Chamber: Senate -->
  <tr>
    <td bgcolor="#dfefff">17.11.2025</td>
    <td>→</td>
    <td>adoptat de Senat</td>
    <td bgcolor="#dfefff">SE</td>
  </tr>

  <!-- Chamber: Chamber of Deputies -->
  <tr>
    <td bgcolor="#fff0d8">19.02.1998</td>
    <td>→</td>
    <td>înregistrat la Camera Deputatilor pentru dezbatere</td>
    <td bgcolor="#fff0d8">CD</td>
  </tr>

  <!-- Chamber: Presidential/Parliament -->
  <tr>
    <td bgcolor="#ffffe8">14.09.1998</td>
    <td>→</td>
    <td>trimitere la Presedintele României pentru promulgare</td>
    <td bgcolor="#ffffe8">PA</td>
  </tr>
</table>
```

## Chamber Color Coding

| Background Color | Chamber | Code |
|-----------------|---------|------|
| `#dfefff` (light blue) | Senate | SE |
| `#fff0d8` (light orange/beige) | Chamber of Deputies | CD |
| `#ffffe8` (light yellow) | Presidential/Parliament | PA |

## Event Types Detected

### Registration Events
- "înregistrat la Camera Deputatilor"
- "înregistrat la Senat"
- "prezentare în Biroul Permanent"

### Committee Actions
- "trimis pentru raport la:" + committee links
- "trimis pentru aviz la:" + committee links
- "primire raport de la:" + PDF/DOC links
- "primire aviz de la:" + PDF/DOC links

### Parliamentary Procedure
- "înscris pe ordinea de zi"
- "dezbatere în plenul" + stenogram/video links
- "adoptat de [Chamber]" + voting details
- With notes: "adoptare cu respectarea prevederilor art.76"

### Presidential Actions
- "depunere la Secretarul general"
- "trimitere la Presedintele României pentru promulgare"
- "Presedintele României solicita reexaminarea"
- "promulgata prin Decret nr.XXX"

### Final Publication
- "devine Legea nr.XXX"
- "publicare lege în Monitorul Oficial nr.XXX"

## Data Model

### Database Tables

#### 1. bill_timeline_events (enhanced)

```sql
CREATE TABLE bill_timeline_events (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    bill_id BIGINT NOT NULL,

    -- Event core data
    event_date DATE,
    event_type VARCHAR(50), -- registered, committee_sent, committee_report, debate, vote, promulgated, etc.
    description TEXT,
    chamber ENUM('senate', 'cdep', 'presidential', 'government'),

    -- Sequencing
    sequence_order INT, -- Order in timeline (1, 2, 3...)
    chamber_round INT, -- Which round in this chamber (for re-examination)

    -- Status indicators
    is_adoption BOOLEAN, -- Is this an adoption/vote event?
    is_final BOOLEAN, -- Is this the final event (publication)?

    -- Voting details (if applicable)
    vote_result VARCHAR(50), -- adoptat, respins, amânarea votului
    vote_details JSON, -- {quorum: "art.76", majority: "2/3", etc.}

    -- Deadlines (from <div id="obs">)
    deadline_date DATE,
    deadline_type VARCHAR(100), -- termen depunere amendamente, termen depunere raport

    -- Associated data
    committees JSON, -- [{name: "...", link: "..."}]
    documents JSON, -- [{title: "...", url: "...", type: "pdf"}]
    stenogram_link VARCHAR(500),
    video_link VARCHAR(500),

    -- Metadata
    scraped_at TIMESTAMP,
    created_at TIMESTAMP,

    FOREIGN KEY (bill_id) REFERENCES legislative_bills(id),
    INDEX idx_bill_sequence (bill_id, sequence_order),
    INDEX idx_event_date (event_date)
);
```

#### 2. bill_timeline_documents

```sql
CREATE TABLE bill_timeline_documents (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    timeline_event_id BIGINT,
    bill_id BIGINT,

    -- Document info
    title VARCHAR(500),
    document_type VARCHAR(100), -- raport, aviz, forma adoptată, stenograma, etc.
    url TEXT,
    local_path VARCHAR(500),

    -- File details
    mime_type VARCHAR(50), -- application/pdf, application/msword
    file_size BIGINT,
    file_hash VARCHAR(64),

    -- Download status
    downloaded BOOLEAN DEFAULT FALSE,
    download_error TEXT,
    downloaded_at TIMESTAMP,

    FOREIGN KEY (timeline_event_id) REFERENCES bill_timeline_events(id),
    FOREIGN KEY (bill_id) REFERENCES legislative_bills(id)
);
```

#### 3. bill_committees (for tracking committee assignments)

```sql
CREATE TABLE bill_committees (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    bill_id BIGINT,
    timeline_event_id BIGINT,

    -- Committee info
    committee_name VARCHAR(255),
    committee_id VARCHAR(50), -- from URL: idc=2
    committee_link TEXT,
    chamber VARCHAR(50),
    legislature VARCHAR(50), -- leg=2024

    -- Assignment type
    assignment_type ENUM('raport', 'aviz'), -- raport = main report, aviz = opinion

    -- Response
    report_received BOOLEAN,
    report_date DATE,
    report_number VARCHAR(50),
    report_url TEXT,
    report_result VARCHAR(100), -- favorabil, nefavorabil, cu amendamente

    -- Deadlines
    deadline_amendments DATE,
    deadline_report DATE,

    FOREIGN KEY (bill_id) REFERENCES legislative_bills(id),
    FOREIGN KEY (timeline_event_id) REFERENCES bill_timeline_events(id)
);
```

## Scraping Implementation

### Step 1: Parse Bill Metadata

```php
protected function parseBillMetadata(Crawler $crawler)
{
    $metadata = [];

    // Extract registration numbers
    $crawler->filter('table tbody tr')->each(function (Crawler $row) use (&$metadata) {
        $cells = $row->filter('td');

        if ($cells->count() >= 2) {
            $label = trim($cells->eq(0)->text());
            $value = trim($cells->eq(1)->text());

            // Registration numbers
            if (stripos($label, 'Camera Deputaţilor') !== false) {
                // Extract: 480/17.11.2025 -> number: 480, date: 2025-11-17
                if (preg_match('/(\d+)\/(\d{2})\.(\d{2})\.(\d{4})/', $value, $m)) {
                    $metadata['cdep_number'] = $m[1];
                    $metadata['cdep_date'] = "{$m[4]}-{$m[3]}-{$m[2]}";
                }
            }

            // Senate number
            if (stripos($label, 'Senat') !== false) {
                $link = $cells->eq(1)->filter('a')->first();
                if ($link->count()) {
                    $metadata['senate_link'] = $link->attr('href');
                    // Extract: L222/24.03.2004
                    if (preg_match('/(L\d+)\/(\d{2})\.(\d{2})\.(\d{4})/', $link->text(), $m)) {
                        $metadata['senate_number'] = $m[1];
                        $metadata['senate_date'] = "{$m[4]}-{$m[3]}-{$m[2]}";
                    }
                }
            }

            // Decision chamber
            if (stripos($label, 'Camera decizionala') !== false) {
                $metadata['decision_chamber'] = $value;
            }

            // Urgency
            if (stripos($label, 'Procedura de urgenta') !== false) {
                $metadata['urgency'] = stripos($value, 'da') !== false;
            }

            // Current status + final law link
            if (stripos($label, 'Stadiu') !== false) {
                $metadata['status'] = $value;

                $lawLink = $cells->eq(1)->filter('a')->first();
                if ($lawLink->count()) {
                    $metadata['final_law_number'] = $lawLink->text();
                    $metadata['final_law_link'] = $lawLink->attr('href');
                }
            }

            // Initiator
            if (stripos($label, 'Initiator') !== false) {
                $metadata['initiator_type'] = $value;
            }
        }
    });

    return $metadata;
}
```

### Step 2: Parse Timeline Events

```php
protected function parseTimelineEvents(Crawler $crawler)
{
    $events = [];
    $sequenceOrder = 1;
    $currentChamber = null;
    $currentRound = []; // Track rounds per chamber

    // Find the timeline table (after "Derularea procedurii legislative")
    $timelineTable = $crawler->filter('table[width="100%"][border="0"][cellspacing="0"]')->last();

    $timelineTable->filter('tr')->each(function (Crawler $row) use (&$events, &$sequenceOrder, &$currentChamber, &$currentRound) {
        $cells = $row->filter('td');

        if ($cells->count() < 2) {
            return; // Skip spacer rows
        }

        // Detect chamber from background color
        $bgColor = $cells->eq(0)->attr('bgcolor');
        $chamber = $this->detectChamberFromColor($bgColor);

        if (!$chamber) {
            return; // Not an event row
        }

        // Update chamber tracking
        if ($chamber !== $currentChamber) {
            $currentChamber = $chamber;
            if (!isset($currentRound[$chamber])) {
                $currentRound[$chamber] = 1;
            } else {
                $currentRound[$chamber]++;
            }
        }

        // Extract event data
        $event = [
            'sequence_order' => $sequenceOrder++,
            'chamber' => $chamber,
            'chamber_round' => $currentRound[$chamber],
        ];

        // Date (first cell)
        $dateText = trim($cells->eq(0)->text());
        if ($dateText && $dateText !== '&nbsp;') {
            $event['event_date'] = $this->parseDate($dateText);
        }

        // Description (usually cells index 2 or 3)
        $descriptionCell = $cells->count() >= 4 ? $cells->eq(2) : $cells->eq(1);
        $event['description'] = trim($descriptionCell->text());

        // Classify event type
        $event['event_type'] = $this->classifyTimelineEvent($event['description']);

        // Check if it's an adoption/vote
        if (stripos($event['description'], 'adoptat') !== false) {
            $event['is_adoption'] = true;
            $event['vote_result'] = 'adoptat';

            // Extract voting details from obs divs
            $obsDiv = $descriptionCell->filter('div#obs')->first();
            if ($obsDiv->count()) {
                $event['vote_details'] = $this->parseVotingDetails($obsDiv->text());
            }
        }

        // Extract deadlines from obs divs
        $descriptionCell->filter('div#obs')->each(function (Crawler $obs) use (&$event) {
            $obsText = $obs->text();

            // Deadline patterns: "termen depunere amendamente: 18.11.2025"
            if (preg_match('/termen\s+([^:]+):\s*(\d{2}\.\d{2}\.\d{4})/', $obsText, $m)) {
                if (!isset($event['deadlines'])) {
                    $event['deadlines'] = [];
                }
                $event['deadlines'][] = [
                    'type' => trim($m[1]),
                    'date' => $this->parseDate($m[2]),
                ];
            }
        });

        // Extract documents (PDFs, DOCs)
        $event['documents'] = [];
        $descriptionCell->filter('a[href*=".pdf"], a[href*=".doc"]')->each(function (Crawler $doc) use (&$event) {
            $href = $doc->attr('href');
            $title = trim($doc->text());

            // Get document type from nearby text or img alt
            $docType = 'other';
            $img = $doc->filter('img')->first();
            if ($img->count()) {
                $alt = $img->attr('alt');
                if (stripos($alt, 'pdf') !== false) {
                    $docType = 'pdf';
                } elseif (stripos($alt, 'doc') !== false) {
                    $docType = 'doc';
                }
            }

            // Classify by title
            if (stripos($title, 'raport') !== false || stripos($title, 'Raport') !== false) {
                $docType = 'raport';
            } elseif (stripos($title, 'aviz') !== false || stripos($title, 'Aviz') !== false) {
                $docType = 'aviz';
            } elseif (stripos($title, 'Forma adoptată') !== false) {
                $docType = 'forma_adoptata';
            }

            $event['documents'][] = [
                'title' => $title,
                'url' => $this->buildFullUrl($href),
                'type' => $docType,
            ];
        });

        // Extract committee links
        $event['committees'] = [];
        $descriptionCell->filter('a[href*="structura2015.co"]')->each(function (Crawler $committee) use (&$event) {
            $href = $committee->attr('href');
            $name = trim($committee->text());

            // Parse committee ID from URL: idc=2&leg=2024&cam=2
            $queryParams = [];
            parse_str(parse_url($href, PHP_URL_QUERY), $queryParams);

            $event['committees'][] = [
                'name' => $name,
                'link' => $this->buildFullUrl($href),
                'committee_id' => $queryParams['idc'] ?? null,
                'legislature' => $queryParams['leg'] ?? null,
                'chamber' => $queryParams['cam'] ?? null,
            ];
        });

        // Extract stenogram link
        $stenogramLink = $descriptionCell->filter('a[href*="steno2015.stenograma"]')->first();
        if ($stenogramLink->count()) {
            $event['stenogram_link'] = $this->buildFullUrl($stenogramLink->attr('href'));
        }

        // Extract video link
        $videoLink = $descriptionCell->filter('a[onclick*="loadintoIframe"]')->first();
        if ($videoLink->count()) {
            $onclick = $videoLink->attr('onclick');
            // Parse: loadintoIframe(1, '/pls/steno/htp_jwplayer?stream=...')
            if (preg_match('/loadintoIframe\(\d+,\s*\'([^\']+)\'/', $onclick, $m)) {
                $event['video_link'] = $this->buildFullUrl($m[1]);
            }
        }

        // Determine if final event (publication in Monitorul Oficial)
        if (stripos($event['description'], 'publicare lege') !== false) {
            $event['is_final'] = true;
        }

        $events[] = $event;
    });

    return $events;
}

protected function detectChamberFromColor($bgColor)
{
    if (!$bgColor) {
        return null;
    }

    // Normalize color (remove #)
    $color = strtolower(str_replace('#', '', $bgColor));

    // Chamber color mapping
    $chamberColors = [
        'dfefff' => 'senate',      // Light blue
        'fff0d8' => 'cdep',        // Light orange/beige
        'ffffe8' => 'presidential', // Light yellow
    ];

    return $chamberColors[$color] ?? null;
}

protected function classifyTimelineEvent($description)
{
    $description = strtolower($description);

    $patterns = [
        'registered' => ['înregistrat', 'prezentare în biroul permanent'],
        'committee_sent' => ['trimis pentru raport', 'trimis pentru aviz'],
        'committee_report' => ['primire raport', 'primire aviz'],
        'agenda' => ['înscris pe ordinea de zi'],
        'debate' => ['dezbatere în plenul'],
        'vote' => ['adoptat', 'respins'],
        'reexamination_request' => ['solicita reexaminarea'],
        'reexamination_vote' => ['adoptat.*ca urmare a cererii de reexaminare'],
        'sent_to_president' => ['trimitere la presedintele româniei'],
        'promulgated' => ['promulgata prin decret'],
        'becomes_law' => ['devine legea'],
        'published' => ['publicare lege în monitorul oficial'],
    ];

    foreach ($patterns as $type => $keywords) {
        foreach ($keywords as $keyword) {
            if (preg_match("/{$keyword}/i", $description)) {
                return $type;
            }
        }
    }

    return 'other';
}
```

### Step 3: Save to Database

```php
public function saveTimelineEvents(LegislativeBill $bill, array $events)
{
    foreach ($events as $eventData) {
        $event = BillTimeline::create([
            'bill_id' => $bill->id,
            'event_date' => $eventData['event_date'] ?? null,
            'event_type' => $eventData['event_type'],
            'description' => $eventData['description'],
            'chamber' => $eventData['chamber'],
            'sequence_order' => $eventData['sequence_order'],
            'chamber_round' => $eventData['chamber_round'],
            'is_adoption' => $eventData['is_adoption'] ?? false,
            'is_final' => $eventData['is_final'] ?? false,
            'vote_result' => $eventData['vote_result'] ?? null,
            'vote_details' => json_encode($eventData['vote_details'] ?? null),
            'committees' => json_encode($eventData['committees'] ?? []),
            'stenogram_link' => $eventData['stenogram_link'] ?? null,
            'video_link' => $eventData['video_link'] ?? null,
        ]);

        // Save documents
        foreach ($eventData['documents'] ?? [] as $doc) {
            BillTimelineDocument::create([
                'timeline_event_id' => $event->id,
                'bill_id' => $bill->id,
                'title' => $doc['title'],
                'document_type' => $doc['type'],
                'url' => $doc['url'],
            ]);
        }

        // Save committee assignments
        foreach ($eventData['committees'] ?? [] as $committee) {
            BillCommittee::create([
                'bill_id' => $bill->id,
                'timeline_event_id' => $event->id,
                'committee_name' => $committee['name'],
                'committee_id' => $committee['committee_id'],
                'committee_link' => $committee['link'],
                'assignment_type' => $this->detectAssignmentType($eventData['event_type']),
            ]);
        }

        // Save deadlines
        foreach ($eventData['deadlines'] ?? [] as $deadline) {
            // Store in event or separate table
            $event->update([
                'deadline_date' => $deadline['date'],
                'deadline_type' => $deadline['type'],
            ]);
        }
    }
}
```

## Frontend Rendering

### Vue.js Component (Timeline Display)

```vue
<template>
  <div class="legislative-timeline">
    <h3>Derularea procedurii legislative</h3>

    <div class="timeline-container">
      <div
        v-for="event in timelineEvents"
        :key="event.id"
        :class="['timeline-event', `chamber-${event.chamber}`]"
      >
        <!-- Chamber indicator -->
        <div class="chamber-badge">
          {{ getChamberCode(event.chamber) }}
        </div>

        <!-- Event content -->
        <div class="event-content">
          <div class="event-date">{{ formatDate(event.event_date) }}</div>
          <div class="event-arrow">→</div>
          <div class="event-description">
            <p>{{ event.description }}</p>

            <!-- Vote result badge -->
            <span v-if="event.is_adoption" class="badge badge-success">
              {{ event.vote_result }}
            </span>

            <!-- Deadlines -->
            <div v-if="event.deadline_date" class="deadline">
              <i class="icon-calendar"></i>
              {{ event.deadline_type }}: {{ formatDate(event.deadline_date) }}
            </div>

            <!-- Committees -->
            <div v-if="event.committees && event.committees.length" class="committees">
              <strong>Comisii:</strong>
              <ul>
                <li v-for="(committee, idx) in event.committees" :key="idx">
                  <a :href="committee.link" target="_blank">{{ committee.name }}</a>
                </li>
              </ul>
            </div>

            <!-- Documents -->
            <div v-if="event.documents && event.documents.length" class="documents">
              <strong>Documente:</strong>
              <ul>
                <li v-for="(doc, idx) in event.documents" :key="idx">
                  <a :href="doc.url" target="_blank" class="doc-link">
                    <i :class="getDocIcon(doc.type)"></i>
                    {{ doc.title }}
                  </a>
                </li>
              </ul>
            </div>

            <!-- Stenogram & Video links -->
            <div v-if="event.stenogram_link || event.video_link" class="media-links">
              <a v-if="event.stenogram_link" :href="event.stenogram_link" target="_blank" class="btn btn-sm">
                <i class="icon-document"></i> Stenogramă
              </a>
              <a v-if="event.video_link" :href="event.video_link" target="_blank" class="btn btn-sm">
                <i class="icon-video"></i> Video
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    billId: {
      type: Number,
      required: true
    }
  },

  data() {
    return {
      timelineEvents: []
    };
  },

  mounted() {
    this.loadTimeline();
  },

  methods: {
    async loadTimeline() {
      const response = await fetch(`/api/bills/${this.billId}/timeline`);
      this.timelineEvents = await response.json();
    },

    getChamberCode(chamber) {
      const codes = {
        'senate': 'SE',
        'cdep': 'CD',
        'presidential': 'PA'
      };
      return codes[chamber] || '';
    },

    formatDate(date) {
      if (!date) return '';
      return new Date(date).toLocaleDateString('ro-RO');
    },

    getDocIcon(type) {
      if (type === 'pdf') return 'icon-pdf';
      if (type === 'doc') return 'icon-doc';
      return 'icon-file';
    }
  }
};
</script>

<style scoped>
.legislative-timeline {
  margin: 20px 0;
}

.timeline-container {
  position: relative;
  padding-left: 60px;
}

.timeline-event {
  position: relative;
  margin-bottom: 20px;
  padding: 15px;
  border-radius: 5px;
  border-left: 4px solid;
}

/* Chamber colors */
.timeline-event.chamber-senate {
  background-color: #dfefff;
  border-left-color: #4a90e2;
}

.timeline-event.chamber-cdep {
  background-color: #fff0d8;
  border-left-color: #e2a54a;
}

.timeline-event.chamber-presidential {
  background-color: #ffffe8;
  border-left-color: #e2d84a;
}

.chamber-badge {
  position: absolute;
  left: -50px;
  top: 15px;
  width: 40px;
  height: 40px;
  background: white;
  border: 2px solid currentColor;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 12px;
}

.event-content {
  display: flex;
  gap: 10px;
  align-items: flex-start;
}

.event-date {
  font-weight: bold;
  min-width: 100px;
  color: #333;
}

.event-arrow {
  color: #666;
  font-weight: bold;
}

.event-description {
  flex: 1;
}

.badge {
  display: inline-block;
  padding: 3px 8px;
  border-radius: 3px;
  font-size: 12px;
  margin-left: 10px;
}

.badge-success {
  background: #28a745;
  color: white;
}

.deadline {
  margin-top: 10px;
  padding: 8px;
  background: #fff;
  border-left: 3px solid #ff6b6b;
  font-size: 13px;
  color: #d63031;
}

.committees ul, .documents ul {
  margin: 10px 0;
  padding-left: 20px;
}

.doc-link {
  color: #0056b3;
  text-decoration: none;
}

.doc-link:hover {
  text-decoration: underline;
}

.media-links {
  margin-top: 10px;
  display: flex;
  gap: 10px;
}

.btn-sm {
  padding: 5px 12px;
  font-size: 13px;
  border-radius: 3px;
  background: #007bff;
  color: white;
  text-decoration: none;
  display: inline-block;
}

.btn-sm:hover {
  background: #0056b3;
}
</style>
```

### API Endpoint

```php
// routes/api.php
Route::get('/bills/{bill}/timeline', function (LegislativeBill $bill) {
    $events = $bill->timelineEvents()
        ->orderBy('sequence_order')
        ->get()
        ->map(function ($event) {
            return [
                'id' => $event->id,
                'event_date' => $event->event_date,
                'event_type' => $event->event_type,
                'description' => $event->description,
                'chamber' => $event->chamber,
                'is_adoption' => $event->is_adoption,
                'vote_result' => $event->vote_result,
                'deadline_date' => $event->deadline_date,
                'deadline_type' => $event->deadline_type,
                'committees' => json_decode($event->committees),
                'documents' => $event->documents,
                'stenogram_link' => $event->stenogram_link,
                'video_link' => $event->video_link,
            ];
        });

    return response()->json($events);
});
```

## Testing Strategy

### 1. Test Multiple Bill Types

Test scraping with various bill patterns:
- **Simple bill**: Single chamber, quick passage
- **Complex bill**: Both chambers, re-examination, amendments
- **Urgent bill**: Fast-track procedure
- **Rejected bill**: Stopped in committee or plenary

### 2. Edge Cases

- Bills with missing dates
- Bills with many committee assignments
- Bills with video/stenogram links
- Historical bills (different HTML structure?)

### 3. Validation

After scraping, verify:
- All events captured in correct order
- Chamber colors correctly identified
- Documents all extracted
- Committee links valid
- Dates properly parsed

## Performance Considerations

### Optimization Strategies

1. **Batch processing**: Process timeline events in bulk inserts
2. **Caching**: Cache parsed HTML to avoid re-parsing
3. **Selective updates**: Only re-scrape timeline if bill status changed
4. **Async document downloads**: Queue document downloads separately

### Database Indexes

```sql
-- For timeline queries
CREATE INDEX idx_bill_timeline_sequence ON bill_timeline_events(bill_id, sequence_order);
CREATE INDEX idx_bill_timeline_chamber ON bill_timeline_events(chamber, event_date);
CREATE INDEX idx_bill_timeline_type ON bill_timeline_events(event_type);

-- For committee lookups
CREATE INDEX idx_bill_committees_bill ON bill_committees(bill_id);
CREATE INDEX idx_bill_committees_name ON bill_committees(committee_name);
```

## Summary

This comprehensive strategy allows us to:

✅ Scrape complete legislative timeline with all details
✅ Preserve chamber flow and visual structure
✅ Capture documents, committees, deadlines, videos
✅ Store in normalized database for querying
✅ Render beautiful timeline in frontend
✅ Support historical analysis and tracking

Next steps:
1. Implement the parsing methods in CDEPScraper
2. Add database migrations for new tables
3. Create API endpoints
4. Build Vue.js timeline component
5. Test with various bill types

---

**Last Updated**: 2025-11-18
**Status**: Ready for implementation
